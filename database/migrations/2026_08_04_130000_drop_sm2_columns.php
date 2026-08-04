<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Removes the SM-2 scheduling columns now that FSRS owns scheduling.
     *
     * Deliberately a separate migration from the one that added the FSRS columns:
     * that one is additive and safe to deploy on its own, so the two can be
     * released in sequence without a window where the running code reads a column
     * that has already been dropped.
     *
     * static_cards loses its scheduling columns entirely. They were shared state on
     * shared content, which meant one learner studying a lesson rewrote the
     * schedule every other learner saw. That state now lives per user on
     * user_static_card_states.
     *
     * `interval` needs quoting on the way back in down(): it is a reserved type
     * keyword in PostgreSQL.
     */
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table): void {
            $table->dropColumn(['interval', 'ease_factor', 'repetitions', 'revised_at', 'last_reviewed']);
        });

        Schema::table('static_cards', function (Blueprint $table): void {
            $table->dropColumn(['interval', 'ease_factor', 'repetitions', 'revised_at', 'last_reviewed']);
        });
    }

    /**
     * Recreates the columns empty. The old values are not recoverable, but
     * review_logs retains the full history either way.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table): void {
            $table->integer('interval')->nullable();
            $table->decimal('ease_factor', 3, 2)->default(2.5);
            $table->integer('repetitions')->default(0);
            $table->timestamp('revised_at')->nullable();
            $table->timestamp('last_reviewed')->nullable();
        });

        Schema::table('static_cards', function (Blueprint $table): void {
            $table->integer('interval')->default(1);
            $table->decimal('ease_factor', 3, 2)->default(2.5);
            $table->integer('repetitions')->default(0);
            $table->timestamp('revised_at')->nullable();
            $table->timestamp('last_reviewed')->nullable();
        });
    }
};
