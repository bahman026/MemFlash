<?php

declare(strict_types=1);

namespace App\Services\GoogleTranslate;

/**
 * What one sync did, counted in rows of the sheet.
 */
final class SyncResult
{
    public function __construct(
        public readonly string $listName,
        // New cards.
        public readonly int $added,
        // Brought in before, or already in the list.
        public readonly int $unchanged,
        // Not an English ↔ Persian pair (another language, a blank cell).
        public readonly int $skipped,
        // New, but the list had no room for them.
        public readonly int $leftOut,
    ) {}

    public function message(): string
    {
        $message = match (true) {
            $this->added > 0 => sprintf('Added %d new %s from Google Translate to %s.', $this->added, $this->words($this->added), $this->listName),
            $this->unchanged > 0 => "No new words in Google Translate: {$this->listName} has them all.",
            default => 'The sheet has no English ↔ Persian words yet.',
        };

        if ($this->leftOut > 0) {
            $message .= sprintf(' %s is full, so %d %s left out.', $this->listName, $this->leftOut, $this->leftOut === 1 ? 'word was' : 'words were');
        }

        if ($this->skipped > 0) {
            $message .= sprintf(' %d %s not English ↔ Persian and %s skipped.', $this->skipped, $this->skipped === 1 ? 'row was' : 'rows were', $this->skipped === 1 ? 'was' : 'were');
        }

        return $message;
    }

    private function words(int $count): string
    {
        return $count === 1 ? 'word' : 'words';
    }
}
