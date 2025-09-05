<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Card;
use App\Models\Deck;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class DeckFileProcessor
{
    /**
     * Process uploaded file and create deck with cards
     */
    public function processFile(UploadedFile $file, array $deckData): Deck
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv') {
            $data = $this->processCsvFile($file);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            $data = $this->processExcelFile($file);
        } else {
            throw new \InvalidArgumentException('Unsupported file format. Please upload CSV or Excel files.');
        }

        // Create the deck
        $deck = Deck::query()->create([
            'name' => $deckData['name'],
            'user_id' => auth()->id(),
            'is_public' => (bool) $deckData['is_public'],
            'new_cards_per_day' => (int) $deckData['new_cards_per_day'],
        ]);

        // Create cards from processed data
        $this->createCardsFromData($deck, $data);

        Log::info("Created deck '{$deck->name}' with {$deck->cards()->count()} cards from file: {$file->getClientOriginalName()}");

        return $deck;
    }

    /**
     * Process CSV file
     */
    private function processCsvFile(UploadedFile $file): array
    {
        $data = [];
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            throw new \RuntimeException('Could not read CSV file.');
        }

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Ensure we have at least 2 columns
            if (count($row) < 2) {
                continue;
            }

            // Check if this row contains "English" and "Persian" as values (Google Sheets structure)
            if (isset($row[0]) && isset($row[1]) &&
                strtolower(trim($row[0])) === 'english' &&
                strtolower(trim($row[1])) === 'persian') {

                // For Google Sheets structure, look for the actual word values
                // The structure is: English, Persian, [actual_english_word], [actual_persian_word]
                if (count($row) >= 4) {
                    // Extract the actual word values from columns 2 and 3
                    $englishWord = trim($row[2] ?? '');
                    $persianWord = trim($row[3] ?? '');
                } else {
                    // Skip this row if it doesn't have enough columns
                    continue;
                }
            } else {
                // Standard two-column format
                $englishWord = trim($row[0] ?? '');
                $persianWord = trim($row[1] ?? '');
            }

            // Skip if either word is empty
            if (empty($englishWord) || empty($persianWord)) {
                continue;
            }

            $data[] = [
                'front' => $englishWord,
                'back' => $persianWord,
            ];
        }

        fclose($handle);

        if (empty($data)) {
            throw new \InvalidArgumentException('No valid data found in CSV file. Please ensure the file has the correct structure with English and Persian words.');
        }

        return $data;
    }

    /**
     * Process Excel file
     */
    private function processExcelFile(UploadedFile $file): array
    {
        try {
            $reader = IOFactory::createReaderForFile($file->getPathname());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $data = [];

            $highestRow = $worksheet->getHighestRow();

            for ($row = 1; $row <= $highestRow; $row++) {
                $cellA = $worksheet->getCell('A' . $row)->getCalculatedValue();
                $cellB = $worksheet->getCell('B' . $row)->getCalculatedValue();
                $cellC = $worksheet->getCell('C' . $row)->getCalculatedValue();
                $cellD = $worksheet->getCell('D' . $row)->getCalculatedValue();

                // Skip empty rows
                if (empty($cellA) && empty($cellB) && empty($cellC) && empty($cellD)) {
                    continue;
                }

                // Check if this row contains "English" and "Persian" as values (Google Sheets structure)
                if (strtolower(trim((string) $cellA)) === 'english' &&
                    strtolower(trim((string) $cellB)) === 'persian') {

                    // For Google Sheets structure, look for the actual word values
                    // The structure is: English, Persian, [actual_english_word], [actual_persian_word]
                    if (! empty($cellC) && ! empty($cellD)) {
                        // Extract the actual word values from columns C and D
                        $englishWord = trim((string) $cellC);
                        $persianWord = trim((string) $cellD);
                    } else {
                        // Skip this row if it doesn't have enough columns
                        continue;
                    }
                } else {
                    // Standard two-column format
                    $englishWord = trim((string) $cellA);
                    $persianWord = trim((string) $cellB);
                }

                // Skip if either word is empty
                if (empty($englishWord) || empty($persianWord)) {
                    continue;
                }

                $data[] = [
                    'front' => $englishWord,
                    'back' => $persianWord,
                ];
            }

            if (empty($data)) {
                throw new \InvalidArgumentException('No valid data found in Excel file. Please ensure the file has the correct structure with English and Persian words.');
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Excel file processing error: ' . $e->getMessage());

            throw new \RuntimeException('Could not process Excel file: ' . $e->getMessage());
        }
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
                'interval' => 1,
                'ease' => 2.5,
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
