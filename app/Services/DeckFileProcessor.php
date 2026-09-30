<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\DeckLimits;
use App\Fsrs\CardState;
use App\Models\Card;
use App\Models\Deck;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DeckFileProcessor
{
    /**
     * Process uploaded file and create deck with cards
     */
    public function processFile(UploadedFile $file, array $deckData): Deck
    {
        $data = $this->extractCards($file);

        // Check if the number of cards exceeds the limit
        if (count($data) > DeckLimits::USER_DECK_MAX_CARDS) {
            throw new \InvalidArgumentException('The file contains ' . count($data) . ' cards, but the maximum allowed is ' . DeckLimits::USER_DECK_MAX_CARDS . ' cards per deck.');
        }

        // Check if user has reached the maximum number of decks
        $userDeckCount = Deck::where('user_id', auth()->id())->count();
        if ($userDeckCount >= DeckLimits::USER_MAX_DECKS) {
            throw new \InvalidArgumentException('You have reached the maximum limit of ' . DeckLimits::USER_MAX_DECKS . ' decks. Please delete some decks before creating new ones.');
        }

        // One transaction, so a failed card insert does not leave an empty deck
        // behind that still counts toward the user's deck limit.
        $deck = DB::transaction(function () use ($deckData, $data): Deck {
            $deck = Deck::query()->create([
                'name' => $deckData['name'],
                'user_id' => auth()->id(),
                'new_cards_per_day' => (int) $deckData['new_cards_per_day'],
            ]);

            $this->createCardsFromData($deck, $data);

            return $deck;
        });

        Log::info("Created deck '{$deck->name}' with {$deck->cards()->count()} cards from file: {$file->getClientOriginalName()}");

        return $deck;
    }

    /**
     * Header cells the importer recognises, by the role they give their column.
     *
     * Checked in this order, and a column keeps the first role it matches, because
     * real headers overlap: "English Meaning" and "English Example" both contain
     * "english", so the more specific roles have to be tried before `front`.
     * Keywords match at the start of a word, so "example" also covers "examples".
     */
    private const HEADER_KEYWORDS = [
        'description' => ['description', 'example', 'sentence', 'note', 'usage', 'context'],
        'back' => ['back', 'persian', 'farsi', 'meaning', 'translation', 'definition', 'answer'],
        'front' => ['front', 'english', 'word', 'phrase', 'term', 'question', 'vocabulary'],
    ];

    /** How many leading rows may be a title or a header before the data starts. */
    private const HEADER_SCAN_ROWS = 5;

    /** Longer than any real header; stops a data row full of keywords being taken for one. */
    private const HEADER_MAX_LENGTH = 50;

    /**
     * Read the cards out of an uploaded CSV or Excel file.
     *
     * @return list<array{front: string, back: string, description: string|null}>
     */
    private function extractCards(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = match (true) {
            $extension === 'csv' => $this->readCsvRows($file),
            in_array($extension, ['xlsx', 'xls'], true) => $this->readExcelRows($file),
            default => throw new \InvalidArgumentException('Unsupported file format. Please upload CSV or Excel files.'),
        };

        $cards = $this->parseRows($rows);

        if ($cards === []) {
            throw new \InvalidArgumentException('No valid cards found in the file. Put the word in the first column and its meaning in the second, or add a header row such as "Word, Meaning, Example".');
        }

        return $cards;
    }

    /**
     * @return list<list<string>>
     */
    private function readCsvRows(UploadedFile $file): array
    {
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            throw new \RuntimeException('Could not read CSV file.');
        }

        $rows = [];

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $rows[] = array_map($this->cellText(...), $row);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @return list<list<string>>
     */
    private function readExcelRows(UploadedFile $file): array
    {
        try {
            $reader = IOFactory::createReaderForFile($file->getPathname());
            $reader->setReadDataOnly(true);
            $worksheet = $reader->load($file->getPathname())->getActiveSheet();

            // Formulas calculated, number formats not applied, plain indexed rows.
            $rows = $worksheet->toArray(null, true, false, false);
        } catch (\Exception $e) {
            Log::error('Excel file processing error: ' . $e->getMessage());

            throw new \RuntimeException('Could not process Excel file: ' . $e->getMessage());
        }

        return array_map(fn (array $row): array => array_map($this->cellText(...), array_values($row)), $rows);
    }

    /**
     * Turn raw rows into cards.
     *
     * Three layouts are understood:
     *   - A header row naming the columns, e.g. "#, English Word / Phrase,
     *     English Meaning, English Example". Columns are matched by name, so their
     *     order does not matter and unrecognised ones (a "#" counter) are ignored.
     *     Anything above the header, such as a title row, is skipped.
     *   - No header: word, meaning and an optional description, in that order.
     *   - The Google Sheets export, where every row is "English, Persian, word,
     *     meaning".
     *
     * A row missing either the word or the meaning is skipped, which also drops
     * footnotes written into a single cell under the table.
     *
     * @param  list<list<string>>  $rows
     * @return list<array{front: string, back: string, description: string|null}>
     */
    private function parseRows(array $rows): array
    {
        $columns = ['front' => 0, 'back' => 1, 'description' => 2];
        $headerFound = false;
        $seenRows = 0;
        $cards = [];

        foreach ($rows as $row) {
            if (implode('', $row) === '') {
                continue;
            }

            $seenRows++;

            if ($this->isGoogleSheetsRow($row)) {
                $cards[] = ['front' => $row[2], 'back' => $row[3], 'description' => null];

                continue;
            }

            if (! $headerFound && $seenRows <= self::HEADER_SCAN_ROWS) {
                $header = $this->headerColumns($row);

                if ($header !== null) {
                    $columns = $header;
                    $headerFound = true;
                    // Everything before the header was a title, not data.
                    $cards = [];

                    continue;
                }
            }

            $front = $row[$columns['front']] ?? '';
            $back = $row[$columns['back']] ?? '';

            if ($front === '' || $back === '') {
                continue;
            }

            $description = $columns['description'] !== null ? ($row[$columns['description']] ?? '') : '';

            $cards[] = [
                'front' => $front,
                'back' => $back,
                'description' => $description === '' ? null : $description,
            ];
        }

        return $cards;
    }

    /**
     * The column index of each role if this row is a header, otherwise null.
     *
     * A header has to name both a word and a meaning column; a description column
     * is optional.
     *
     * @param  list<string>  $row
     * @return array{front: int, back: int, description: int|null}|null
     */
    private function headerColumns(array $row): ?array
    {
        $found = [];

        foreach ($row as $index => $cell) {
            if ($cell === '' || mb_strlen($cell) > self::HEADER_MAX_LENGTH) {
                continue;
            }

            $label = mb_strtolower($cell);

            foreach (self::HEADER_KEYWORDS as $role => $keywords) {
                if (isset($found[$role])) {
                    continue;
                }

                foreach ($keywords as $keyword) {
                    if (preg_match('/(?<![\p{L}\p{N}])' . preg_quote($keyword, '/') . '/u', $label) === 1) {
                        $found[$role] = $index;

                        continue 3;
                    }
                }
            }
        }

        if (! isset($found['front'], $found['back'])) {
            return null;
        }

        return [
            'front' => $found['front'],
            'back' => $found['back'],
            'description' => $found['description'] ?? null,
        ];
    }

    /**
     * @param  list<string>  $row
     */
    private function isGoogleSheetsRow(array $row): bool
    {
        return mb_strtolower($row[0] ?? '') === 'english'
            && mb_strtolower($row[1] ?? '') === 'persian'
            && ($row[2] ?? '') !== ''
            && ($row[3] ?? '') !== '';
    }

    /**
     * One cell as trimmed text.
     *
     * Strips a UTF-8 byte-order mark, which Excel's "CSV UTF-8" puts in front of
     * the first cell and trim() does not remove; left in, it stops the header
     * being recognised and ends up inside the first card's text.
     */
    private function cellText(mixed $value): string
    {
        if ($value === null || is_bool($value)) {
            return '';
        }

        // A whole number stored as a float (a "#" column) reads as "1", not "1.0".
        if (is_float($value) && floor($value) === $value) {
            $value = (int) $value;
        }

        $text = preg_replace('/^\x{FEFF}/u', '', (string) $value) ?? (string) $value;

        return trim($text);
    }

    /**
     * Import cards to existing deck (update existing, append new)
     */
    public function importToExistingDeck(UploadedFile $file, Deck $deck): array
    {
        $data = $this->extractCards($file);

        $newCards = 0;
        $updatedCards = 0;
        $skippedCards = 0;
        $duplicateRows = 0;

        Log::info("Starting import to deck '{$deck->name}' with " . count($data) . " rows from file: {$file->getClientOriginalName()}");

        // Check for duplicate rows in the file data
        $seenFronts = [];
        $uniqueData = [];

        foreach ($data as $cardData) {
            if (empty($cardData['front']) || empty($cardData['back'])) {
                $skippedCards++;

                continue;
            }

            // Check for duplicates within the file
            if (isset($seenFronts[$cardData['front']])) {
                $duplicateRows++;
                Log::debug("Duplicate row in file: '{$cardData['front']}'");

                continue;
            }

            $seenFronts[$cardData['front']] = true;
            $uniqueData[] = $cardData;
        }

        Log::info('After removing duplicates: ' . count($uniqueData) . " unique rows, {$duplicateRows} duplicates removed");

        // Check if adding new cards would exceed the deck limit
        $currentCardCount = $deck->cards()->count();
        $newCardsToAdd = count($uniqueData);

        if ($currentCardCount + $newCardsToAdd > DeckLimits::USER_DECK_MAX_CARDS) {
            $availableSlots = DeckLimits::USER_DECK_MAX_CARDS - $currentCardCount;

            throw new \InvalidArgumentException("Adding {$newCardsToAdd} cards would exceed the deck limit of " . DeckLimits::USER_DECK_MAX_CARDS . " cards. You can only add {$availableSlots} more cards to this deck.");
        }

        foreach ($uniqueData as $cardData) {
            // Check if a card with this front text already exists in the deck
            $existingCard = Card::query()
                ->where('deck_id', $deck->id)
                ->where('front', $cardData['front'])
                ->first();

            if ($existingCard) {
                // Update existing card. A file with no description for this row
                // leaves the card's own description alone rather than wiping it.
                $changes = ['back' => $cardData['back'], 'updated_at' => now()];

                if ($cardData['description'] !== null) {
                    $changes['description'] = $cardData['description'];
                }

                $existingCard->update($changes);
                $updatedCards++;
                Log::debug("Updated existing card: '{$cardData['front']}' -> '{$cardData['back']}'");
            } else {
                // Create new card
                Card::query()->create([
                    'deck_id' => $deck->id,
                    'front' => $cardData['front'],
                    'back' => $cardData['back'],
                    'description' => $cardData['description'],
                    // No memory state: FSRS derives stability and difficulty from
                    // the first rating, so an imported card starts as New.
                    'state' => CardState::New,
                    'due' => now(),
                ]);
                $newCards++;
                Log::debug("Created new card: '{$cardData['front']}' -> '{$cardData['back']}'");
            }
        }

        $totalProcessed = $newCards + $updatedCards + $skippedCards;
        Log::info("Import completed for deck '{$deck->name}': {$newCards} new cards, {$updatedCards} updated cards, {$skippedCards} skipped cards, {$duplicateRows} duplicate rows removed. Total processed: {$totalProcessed} from " . count($data) . ' rows');

        return [
            'new_cards' => $newCards,
            'updated_cards' => $updatedCards,
            'skipped_cards' => $skippedCards,
            'duplicate_rows' => $duplicateRows,
            'total_processed' => $totalProcessed,
            'total_rows' => count($data),
        ];
    }

    /**
     * Create cards from processed data
     */
    private function createCardsFromData(Deck $deck, array $data): void
    {
        $cardsToCreate = [];

        foreach ($data as $cardData) {
            // Skip if front or back is empty
            if (empty($cardData['front']) || empty($cardData['back'])) {
                continue;
            }

            $cardsToCreate[] = [
                'deck_id' => $deck->id,
                'front' => $cardData['front'],
                'back' => $cardData['back'],
                'description' => $cardData['description'],
                'state' => CardState::New->value,
                'due' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($cardsToCreate)) {
            throw new \InvalidArgumentException('No valid cards could be created from the file. Please ensure the file contains valid data in the first two columns.');
        }

        // Bulk insert cards for better performance
        Card::query()->insert($cardsToCreate);
    }

    /**
     * Validate file before processing
     */
    public function validateFile(UploadedFile $file): array
    {
        $errors = [];

        // Check file size (max 10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            $errors[] = 'File size must be less than 10MB.';
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['csv', 'xlsx', 'xls'])) {
            $errors[] = 'File must be a CSV or Excel file (.csv, .xlsx, .xls).';
        }

        // Check if file is readable
        if (! $file->isValid()) {
            $errors[] = 'File upload failed. Please try again.';
        }

        return $errors;
    }
}
