<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A user's link to the Google Sheet that Google Translate's Saved page
     * exports to, and the word list its words go into. One per user.
     *
     * deck_id null means the default list. Deleting the list falls back to it
     * rather than deleting the link.
     *
     * imported_words remembers every word already brought in (lowercased
     * fronts), so a word the user deletes from the list is not added back by
     * the next sync.
     */
    public function up(): void
    {
        Schema::create('google_translate_syncs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('deck_id')->nullable()->constrained()->nullOnDelete();
            $table->text('sheet_url');
            $table->jsonb('imported_words')->default('[]');
            $table->timestamp('last_synced_at')->nullable();
            $table->unsignedInteger('last_added')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_translate_syncs');
    }
};
