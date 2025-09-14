<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Card;
use App\Models\Deck;
use App\Models\StaticCard;
use App\Models\StaticDeck;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeckCsvExportService
{
    /**
     * Export a user deck to CSV
     */
    public function exportUserDeck(Deck $deck): StreamedResponse
    {
        $cards = $deck->cards()->orderBy('created_at')->get();
        
        return $this->generateCsvResponse(
            $cards,
            $deck->name,
            'user_deck'
        );
    }


    /**
     * Generate CSV response from cards collection
     */
    private function generateCsvResponse(Collection $cards, string $deckName, string $deckType): StreamedResponse
    {
        $filename = $this->generateFilename($deckName, $deckType);
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $callback = function () use ($cards, $deckType) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 to ensure proper encoding in Excel
            fwrite($file, "\xEF\xBB\xBF");
            
            // Write CSV headers
            $this->writeCsvHeaders($file, $deckType);
            
            // Write card data
            foreach ($cards as $card) {
                $this->writeCardRow($file, $card, $deckType);
            }
            
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Write CSV headers based on deck type
     */
    private function writeCsvHeaders($file, string $deckType): void
    {
        $headers = [
            'Front',
            'Back'
        ];

        fputcsv($file, $headers);
    }

    /**
     * Write a single card row to CSV
     */
    private function writeCardRow($file, $card, string $deckType): void
    {
        $row = [
            $card->front,
            $card->back
        ];

        fputcsv($file, $row);
    }

    /**
     * Generate a safe filename for the CSV export
     */
    private function generateFilename(string $deckName, string $deckType): string
    {
        // Clean the deck name for filename
        $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $deckName);
        $cleanName = preg_replace('/_+/', '_', $cleanName); // Remove multiple underscores
        $cleanName = trim($cleanName, '_'); // Remove leading/trailing underscores
        
        $timestamp = now()->format('Y-m-d_H-i-s');
        $type = $deckType === 'static_deck' ? 'static' : 'user';
        
        return "memflash_{$type}_deck_{$cleanName}_{$timestamp}.csv";
    }

    /**
     * Export multiple decks as separate CSV files in a ZIP archive
     */
    public function exportMultipleDecks(array $deckIds, string $type = 'user'): Response
    {
        $zip = new \ZipArchive();
        $zipFilename = 'memflash_decks_export_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $tempPath = sys_get_temp_dir() . '/' . $zipFilename;

        if ($zip->open($tempPath, \ZipArchive::CREATE) !== TRUE) {
            abort(500, 'Cannot create ZIP file');
        }

        if ($type === 'user') {
            $decks = Deck::whereIn('id', $deckIds)->with('cards')->get();
        } else {
            $decks = StaticDeck::whereIn('id', $deckIds)->with('cards')->get();
        }

        foreach ($decks as $deck) {
            $csvContent = $this->generateCsvContent($deck->cards, $type);
            $filename = $this->generateFilename($deck->name, $type);
            $zip->addFromString($filename, $csvContent);
        }

        $zip->close();

        return response()->download($tempPath, $zipFilename)->deleteFileAfterSend(true);
    }

    /**
     * Generate CSV content as string
     */
    private function generateCsvContent(Collection $cards, string $deckType): string
    {
        $output = fopen('php://temp', 'r+');
        
        // Add BOM for UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // Write headers
        $this->writeCsvHeaders($output, $deckType);
        
        // Write card data
        foreach ($cards as $card) {
            $this->writeCardRow($output, $card, $deckType);
        }
        
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);
        
        return $content;
    }
}
