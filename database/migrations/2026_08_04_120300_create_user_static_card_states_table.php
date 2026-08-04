<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-user FSRS state for shared curriculum cards.
     *
     * static_cards is global content, but memory state belongs to one person: two
     * learners cannot share a stability value. Until now the SRS columns lived on
     * static_cards itself, so studying a lesson -- or resetting it -- rewrote the
     * schedule every other user saw. Rows here are created on demand the first
     * time a user answers a card.
     *
     * The old columns on static_cards are dropped by a follow-up migration, once
     * the controllers no longer read them.
     */
    public function up(): void
    {
        Schema::create('user_static_card_states', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('static_card_id')->constrained()->cascadeOnDelete();
            $table->string('state', 12)->default('new');
            $table->smallInteger('step')->nullable();
            $table->double('stability')->nullable();
            $table->double('difficulty')->nullable();
            $table->timestamp('due')->nullable();
            $table->timestamp('last_review')->nullable();
            $table->integer('reps')->default(0);
            $table->integer('lapses')->default(0);
            $table->boolean('suspended')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'static_card_id']);

            // Drives the due-queue query.
            $table->index(['user_id', 'state', 'due']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_static_card_states');
    }
};
