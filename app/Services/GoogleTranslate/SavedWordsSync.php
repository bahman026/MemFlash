<?php

declare(strict_types=1);

namespace App\Services\GoogleTranslate;

use App\Models\GoogleTranslateSync;
use App\Services\WordListService;
use App\Services\WordLookup\Language;
use App\Services\WordLookup\Text;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Brings the words saved in Google Translate into a word list.
 *
 * Google Translate has no API for its saved words. Its Saved page can only
 * export them to a Google Sheet, one row per word: source language, target
 * language, source text, translation. The user shares that sheet by link (or
 * publishes it) and pastes the link here once; every sync reads it as CSV.
 *
 * A sync only adds. A word removed in Google Translate keeps its card and its
 * study history, and a card the user deletes here is not brought back: every
 * word ever imported is remembered in imported_words.
 */
class SavedWordsSync
{
    /**
     * A sheet of 2,000 words is ~100 KB. Anything far bigger is not one.
     */
    private const MAX_BYTES = 5 * 1024 * 1024;

    private const MAX_TEXT = 1000;

    public function __construct(
        private readonly WordListService $lists,
        private readonly int $timeout = 20,
    ) {}

    /**
     * Reads the sheet and adds the words the list does not have yet.
     *
     * A failure is recorded on the sync (and shown on the dashboard) before
     * it is thrown.
     *
     * @throws SheetUnavailable
     */
    public function sync(GoogleTranslateSync $sync): SyncResult
    {
        try {
            $rows = $this->fetch($sync->sheet_url);
        } catch (SheetUnavailable $e) {
            $sync->update(['last_error' => $e->getMessage()]);

            throw $e;
        }

        // Fetched outside the transaction, applied inside it under a row lock,
        // so the daily run and a click on "Sync now" cannot both add a word.
        return DB::transaction(function () use ($sync, $rows): SyncResult {
            $sync = GoogleTranslateSync::query()->whereKey($sync->id)->lockForUpdate()->firstOrFail();
            $list = $sync->deck ?? $this->lists->defaultListFor($sync->user);

            $imported = array_fill_keys($sync->imported_words, true);
            $inList = $list->cards()->pluck('front')
                ->mapWithKeys(fn (string $front): array => [mb_strtolower($front) => true])
                ->all();
            $slots = $list->getRemainingCardSlots();
            $added = $unchanged = $skipped = $leftOut = 0;

            foreach ($rows as $row) {
                $pair = $this->pair($row);

                if ($pair === null) {
                    $skipped++;

                    continue;
                }

                [$front, $back] = $pair;
                $key = mb_strtolower($front);

                if (isset($imported[$key]) || isset($inList[$key])) {
                    $imported[$key] = true;
                    $unchanged++;

                    continue;
                }

                if ($slots <= 0) {
                    $leftOut++;

                    continue;
                }

                $this->lists->addWord($list, $front, $back);
                $imported[$key] = $inList[$key] = true;
                $slots--;
                $added++;
            }

            $sync->update([
                // array_keys() turns numeric keys ("747") back into ints.
                'imported_words' => array_map('strval', array_keys($imported)),
                'last_synced_at' => now(),
                'last_added' => $added,
                'last_error' => null,
            ]);

            return new SyncResult($list->name, $added, $unchanged, $skipped, $leftOut);
        });
    }

    /**
     * The sheet's rows, as CSV.
     *
     * @return list<list<string|null>>
     *
     * @throws SheetUnavailable
     */
    public function fetch(string $url): array
    {
        $link = SheetLink::parse($url) ?? throw new SheetUnavailable('That is not a Google Sheets link.');

        try {
            $response = Http::timeout($this->timeout)->get($link->csvUrl);
        } catch (ConnectionException) {
            throw new SheetUnavailable('Google Sheets did not answer. Try again in a minute.');
        }

        if (in_array($response->status(), [401, 403], true)) {
            throw new SheetUnavailable($this->notSharedMessage());
        }

        if ($response->status() === 404) {
            throw new SheetUnavailable('Google Sheets cannot find that sheet. Was it deleted?');
        }

        if (! $response->successful()) {
            throw new SheetUnavailable('Google Sheets could not export the sheet. Try again later.');
        }

        // A sheet that is not shared redirects to Google's sign-in page, which
        // arrives as a 200 of HTML rather than an error.
        if (str_contains(strtolower($response->header('Content-Type')), 'text/html')) {
            throw new SheetUnavailable($this->notSharedMessage());
        }

        $body = $response->body();

        if (strlen($body) > self::MAX_BYTES) {
            throw new SheetUnavailable('That sheet is too big to be a list of saved words.');
        }

        return $this->parseCsv($body);
    }

    /**
     * @return list<list<string|null>>
     */
    public function parseCsv(string $csv): array
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, preg_replace('/^\x{FEFF}/u', '', $csv) ?? $csv);
        rewind($stream);

        $rows = [];
        while (($row = fgetcsv($stream, escape: '')) !== false) {
            if ($row !== [null]) {
                $rows[] = $row;
            }
        }

        fclose($stream);

        return $rows;
    }

    /**
     * A row as [English, Persian], or null when it is not that pair.
     *
     * The words are the last two cells: Google Translate's export has the
     * language names first (in the user's interface language, so possibly in
     * Persian script themselves), and a two-column sheet typed by hand works
     * the same. Which is which is decided by script, so words saved from
     * Persian to English come out the same way round as English to Persian.
     *
     * @param  list<string|null>  $row
     * @return array{string, string}|null
     */
    public function pair(array $row): ?array
    {
        if (count($row) < 2) {
            return null;
        }

        [$a, $b] = array_map(fn (?string $cell): string => Text::clean((string) $cell), array_slice($row, -2));

        if ($a === '' || $b === '' || mb_strlen($a) > self::MAX_TEXT || mb_strlen($b) > self::MAX_TEXT) {
            return null;
        }

        $pair = match ([Language::detect($a), Language::detect($b)]) {
            [Language::English, Language::Persian] => [$a, $b],
            [Language::Persian, Language::English] => [$b, $a],
            default => null,
        };

        // detect() calls anything without Arabic script English; a word saved
        // from Japanese or Russian is not.
        return $pair !== null && preg_match('/\p{Latin}/u', $pair[0]) === 1 ? $pair : null;
    }

    private function notSharedMessage(): string
    {
        return 'MemFlash cannot open that sheet. In Google Sheets choose Share → General access → "Anyone with the link", then sync again.';
    }
}
