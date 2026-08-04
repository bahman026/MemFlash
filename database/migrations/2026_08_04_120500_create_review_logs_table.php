<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every review, forever. APPEND ONLY.
     *
     * Never update or delete a row: the optimizer replays this table to reconstruct
     * each card's memory history, so its correctness depends on the log being a
     * faithful record. Undo is implemented by appending a compensating entry, not
     * by removing the original.
     *
     * A review that is not logged can never contribute to personalising the
     * parameters, which is why the scheduler returns the log payload rather than
     * leaving it to callers to remember.
     *
     * The subject is polymorphic because this app has two card hierarchies:
     * personal Card rows and shared StaticCard rows. user_id is stored explicitly
     * rather than derived, since a static card belongs to no single user.
     */
    public function up(): void
    {
        Schema::create('review_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('reviewable');                       // Card | StaticCard

            $table->unsignedTinyInteger('rating');               // 1..4
            $table->string('state_before', 12);
            $table->integer('elapsed_days');
            $table->double('retrievability_before');
            $table->double('stability_after');
            $table->double('difficulty_after');
            $table->integer('scheduled_days');
            $table->integer('review_duration_ms')->nullable();
            $table->timestamp('reviewed_at');

            /**
             * Client-generated id for offline reviews. Unique, so replaying a sync
             * that was interrupted mid-flight cannot apply the same review twice.
             * Null for reviews made while online.
             */
            $table->uuid('client_uuid')->nullable()->unique();

            /** True when the review arrived from an offline queue rather than live. */
            $table->boolean('synced_offline')->default(false);

            $table->timestamp('created_at')->nullable();

            // The optimizer replays a card's history in order.
            $table->index(['reviewable_type', 'reviewable_id', 'reviewed_at'], 'review_logs_subject_time_idx');
            // Per-user stats and the "enough reviews to optimize?" threshold check.
            $table->index(['user_id', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_logs');
    }
};
