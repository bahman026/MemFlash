<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\GoogleTranslateSync;
use App\Services\GoogleTranslate\SavedWordsSync;
use App\Services\GoogleTranslate\SheetUnavailable;
use Illuminate\Console\Command;

class SyncGoogleTranslateCommand extends Command
{
    protected $signature = 'words:sync-google-translate
        {--user= : Restrict to one user id}';

    protected $description = 'Bring words saved in Google Translate into each user\'s word list';

    public function handle(SavedWordsSync $syncer): int
    {
        $syncs = GoogleTranslateSync::query()
            ->with('user')
            ->when($this->option('user'), fn ($q, $id) => $q->where('user_id', $id))
            ->get();

        foreach ($syncs as $sync) {
            if ($sync->user->isBlocked()) {
                continue;
            }

            try {
                $result = $syncer->sync($sync);
                $this->line("  user {$sync->user_id}: {$result->message()}");
            } catch (SheetUnavailable $e) {
                // Recorded on the sync and shown on the user's dashboard.
                $this->line("  <fg=yellow>user {$sync->user_id}</>: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
