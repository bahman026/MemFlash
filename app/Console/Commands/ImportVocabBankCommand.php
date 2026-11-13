<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\WordLookup\VocabBank;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportVocabBankCommand extends Command
{
    protected $signature = 'words:import-vocab-bank
        {--file= : A kaikki.org Persian JSON-lines file (.jsonl or .jsonl.gz) instead of downloading it}';

    protected $description = 'Download Wiktionary\'s Persian dictionary into the English ↔ Persian word bank';

    public function handle(VocabBank $bank): int
    {
        $file = $this->option('file');
        $download = null;

        if (! is_string($file) || $file === '') {
            $download = tempnam(sys_get_temp_dir(), 'vocab-bank-');
            $this->line('Downloading ' . VocabBank::SOURCE . ' …');

            $response = Http::timeout(300)->sink($download)->get(VocabBank::SOURCE);
            if (! $response->successful()) {
                @unlink($download);
                $this->error("Download failed: HTTP {$response->status()}.");

                return self::FAILURE;
            }

            $file = $download;
        }

        try {
            $count = $bank->import($file);
        } finally {
            if ($download !== null) {
                @unlink($download);
            }
        }

        $this->info("The word bank now holds {$count} English ↔ Persian pairs.");

        return self::SUCCESS;
    }
}
