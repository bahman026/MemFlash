<?php

declare(strict_types=1);

namespace App\Services\WordLookup;

use Illuminate\Support\Facades\DB;

/**
 * An English ↔ Persian word bank kept on the server, so a lookup needs no
 * outside translator.
 *
 * Since 2026-11-13 the server cannot reach one from Iran: MyMemory never
 * answers it and Google sends it to a captcha page. The bank comes from
 * Wiktionary's Persian dictionary as extracted by kaikki.org (about 20,000
 * Persian words with English meanings, 13.6 MB gzipped, CC BY-SA). Each
 * Persian word is indexed under its English meanings, so it answers both
 * directions: "خنجر" → dagger, and "dagger" → خنجر.
 *
 * Only the Persian dictionary: English Wiktionary's Persian translation
 * tables would add more English words but come in a 3.4 GB file.
 */
final class VocabBank
{
    public const SOURCE = 'https://kaikki.org/dictionary/Persian/kaikki.org-dictionary-Persian.jsonl.gz';

    public const TABLE = 'vocab_bank';

    /** Senses with these tags are not how Persian is spoken in Iran today. */
    private const SKIPPED_TAGS = [
        'archaic', 'obsolete', 'rare', 'dated', 'historical', 'poetic', 'literary', 'dialectal',
        'Classical-Persian', 'Early-New-Persian', 'Dari', 'Tajik',
    ];

    /** Inflections and spellings point at another entry; that entry is indexed. */
    private const REFERENCE_TAGS = ['form-of', 'alt-of'];

    /** Parts of speech that are not vocabulary. */
    private const SKIPPED_POS = ['name', 'suffix', 'prefix', 'infix', 'character', 'symbol', 'romanization', 'punct'];

    /** An English meaning longer than this is a definition, not a translation. */
    private const MAX_ENGLISH_WORDS = 3;

    /**
     * English matching form: lower case, without a leading "to", "a", "an" or
     * "the", so "To Win" finds "win". The device's dictionary does the same
     * (resources/js/offline/dictionary.js → matchKey).
     */
    public static function englishKey(string $text): string
    {
        $text = mb_strtolower(Text::clean($text));

        return preg_replace('/^(?:to|a|an|the)\s+/u', '', $text) ?? $text;
    }

    /**
     * Persian matching form: no diacritics, and no spaces or zero-width
     * non-joiners, which people type interchangeably ("هیجان‌زده" / "هیجان زده").
     */
    public static function persianKey(string $text): string
    {
        $text = Text::clean($text);
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text) ?? $text;

        return preg_replace('/[\s\x{200C}\x{200F}]+/u', '', $text) ?? $text;
    }

    /**
     * The rows for one kaikki.org entry (a decoded JSON line), ready to insert.
     *
     * rank, lowest first, puts the likeliest answer for a key on top: a
     * meaning without a parenthetical qualifier ("go" from "go (board game)"
     * is not "go"), from an earlier sense, given as the only meaning, and
     * from a Persian word with more senses and derived words (a rough
     * stand-in for how common it is, which Wiktionary does not record).
     *
     * @param  array<string, mixed>  $entry
     * @return list<array{english: string, english_key: string, persian: string, persian_key: string, part_of_speech: string|null, rank: int}>
     */
    public static function rowsFor(array $entry): array
    {
        $persian = is_string($entry['word'] ?? null) ? Text::clean($entry['word']) : '';
        $pos = is_string($entry['pos'] ?? null) ? $entry['pos'] : null;

        if (($entry['lang_code'] ?? null) !== 'fa' || $persian === '' || in_array($pos, self::SKIPPED_POS, true)
            || Language::detect($persian) !== Language::Persian || mb_strlen($persian) > 100) {
            return [];
        }

        $senses = array_values(array_filter(
            is_array($entry['senses'] ?? null) ? $entry['senses'] : [],
            fn (mixed $sense): bool => is_array($sense) && self::usable($sense),
        ));

        $popularity = count($senses) + count(is_array($entry['derived'] ?? null) ? $entry['derived'] : []);
        foreach ($senses as $sense) {
            $popularity += count(is_array($sense['derived'] ?? null) ? $sense['derived'] : []);
        }

        $rows = [];

        foreach ($senses as $senseIndex => $sense) {
            $gloss = is_string($sense['glosses'][0] ?? null) ? $sense['glosses'][0] : '';
            $qualified = str_contains($gloss, '(');
            $plain = trim(preg_replace('/\([^)]*\)/u', '', $gloss) ?? $gloss);
            $meanings = array_values(array_filter(array_map('trim', preg_split('/[;,]/u', $plain) ?: [])));

            foreach ($meanings as $position => $meaning) {
                $english = Text::cleanTranslation($meaning, Language::English);
                $key = self::englishKey($english);

                if ($key === '' || Language::detect($english) !== Language::English
                    || count(explode(' ', $key)) > self::MAX_ENGLISH_WORDS || mb_strlen($english) > 100) {
                    continue;
                }

                $rows[$key] ??= [
                    'english' => $english,
                    'english_key' => $key,
                    'persian' => $persian,
                    'persian_key' => self::persianKey($persian),
                    'part_of_speech' => $pos,
                    'rank' => ($qualified ? 100_000 : 0)
                        + min($senseIndex, 3) * 10_000
                        + (count($meanings) === 1 ? 0 : 1_000)
                        + min($position, 9) * 100
                        + (99 - min($popularity, 99)),
                ];
            }
        }

        return array_values($rows);
    }

    /** @param  array<string, mixed>  $sense */
    private static function usable(array $sense): bool
    {
        $tags = is_array($sense['tags'] ?? null) ? $sense['tags'] : [];

        return array_intersect($tags, [...self::SKIPPED_TAGS, ...self::REFERENCE_TAGS]) === []
            && empty($sense['form_of']) && empty($sense['alt_of']);
    }

    /**
     * Replaces the bank with the entries in a kaikki.org JSON-lines file
     * (gzipped or not), in one transaction, so lookups never see it half
     * full. Returns the number of rows.
     */
    public function import(string $path): int
    {
        $handle = gzopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException("Could not open {$path}.");
        }

        $rows = [];
        $seen = [];

        try {
            while (($line = gzgets($handle)) !== false) {
                $entry = json_decode($line, true);
                if (! is_array($entry)) {
                    continue;
                }

                foreach (self::rowsFor($entry) as $row) {
                    // One Persian word can have several entries (etymologies).
                    $pair = $row['persian_key'] . '|' . $row['english_key'];
                    if (isset($seen[$pair]) && $rows[$seen[$pair]]['rank'] <= $row['rank']) {
                        continue;
                    }

                    if (isset($seen[$pair])) {
                        $rows[$seen[$pair]] = $row;
                    } else {
                        $seen[$pair] = count($rows);
                        $rows[] = $row;
                    }
                }
            }
        } finally {
            gzclose($handle);
        }

        DB::transaction(function () use ($rows): void {
            DB::table(self::TABLE)->delete();

            foreach (array_chunk($rows, 1000) as $chunk) {
                DB::table(self::TABLE)->insert($chunk);
            }
        });

        return count($rows);
    }

    /**
     * Persian for an English word, or English for a Persian one, best first.
     *
     * @return list<string>
     */
    public function translate(string $text, Language $from, int $limit): array
    {
        $english = $from === Language::English;
        $key = $english ? self::englishKey($text) : self::persianKey($text);

        if ($key === '') {
            return [];
        }

        return DB::table(self::TABLE)
            ->where($english ? 'english_key' : 'persian_key', $key)
            ->orderBy('rank')
            ->orderBy('id')
            ->limit($limit * 3)
            ->pluck($english ? 'persian' : 'english')
            ->unique()
            ->take($limit)
            ->values()
            ->all();
    }
}
