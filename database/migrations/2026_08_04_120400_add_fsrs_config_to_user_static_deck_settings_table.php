<?php

declare(strict_types=1);

use App\Fsrs\SchedulerConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The scheduling preset for a static deck.
     *
     * Personal decks keep theirs in deck_configs, keyed by deck. Static decks are
     * shared content studied on a per-user schedule, so their preset lives on the
     * existing per-user settings row instead. Both resolve to the same
     * App\Fsrs\SchedulerConfig.
     *
     * parameters is nullable: null means "use the FSRS-6 defaults", so an
     * unoptimized deck stores no weights at all.
     */
    public function up(): void
    {
        Schema::table('user_static_deck_settings', function (Blueprint $table): void {
            $table->json('parameters')->nullable()->after('cards_per_day');
            $table->decimal('desired_retention', 4, 3)
                ->default(SchedulerConfig::DEFAULT_DESIRED_RETENTION)
                ->after('parameters');
            $table->json('learning_steps')->nullable()->after('desired_retention');
            $table->json('relearning_steps')->nullable()->after('learning_steps');
            $table->integer('maximum_interval')
                ->default(SchedulerConfig::DEFAULT_MAXIMUM_INTERVAL)
                ->after('relearning_steps');
            $table->boolean('enable_fuzzing')->default(true)->after('maximum_interval');
            $table->timestamp('optimized_at')->nullable()->after('enable_fuzzing');
            $table->integer('optimized_review_count')->nullable()->after('optimized_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_static_deck_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'parameters', 'desired_retention', 'learning_steps', 'relearning_steps',
                'maximum_interval', 'enable_fuzzing', 'optimized_at', 'optimized_review_count',
            ]);
        });
    }
};
