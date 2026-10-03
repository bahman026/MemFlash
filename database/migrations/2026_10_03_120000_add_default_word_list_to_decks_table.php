<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A word list is a deck: a word looked up and added becomes a card, so it is
     * studied, scheduled, exported and synced like any other. All a list needs
     * on top of that is a marker for the one deck that words go into when the
     * user has not picked another.
     *
     * The partial unique index allows exactly one default list per user. It is
     * what makes WordListService::defaultListFor() safe from two tabs racing to
     * create it: the loser's insert fails and firstOrCreate reads the winner's row.
     */
    public function up(): void
    {
        Schema::table('decks', function (Blueprint $table): void {
            $table->boolean('is_default_list')->default(false)->after('is_public');
        });

        DB::statement('CREATE UNIQUE INDEX decks_one_default_list_per_user ON decks (user_id) WHERE is_default_list');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS decks_one_default_list_per_user');

        Schema::table('decks', function (Blueprint $table): void {
            $table->dropColumn('is_default_list');
        });
    }
};
