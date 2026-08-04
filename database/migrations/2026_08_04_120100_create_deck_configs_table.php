<?php

declare(strict_types=1);

use App\Fsrs\Parameters;
use App\Fsrs\SchedulerConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One scheduling preset per personal deck.
     *
     * Parameters are stored per deck, never globally: different material produces
     * different memory behaviour, so a single optimized set cannot serve every
     * deck. Static decks keep their preset on user_static_deck_settings instead,
     * because their scheduling state is per user.
     */
    public function up(): void
    {
        Schema::create('deck_configs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('deck_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('parameters');                                 // 21 floats
            $table->decimal('desired_retention', 4, 3)->default(0.900);
            $table->json('learning_steps');                             // seconds
            $table->json('relearning_steps');                           // seconds
            $table->integer('maximum_interval')->default(SchedulerConfig::DEFAULT_MAXIMUM_INTERVAL);
            $table->boolean('enable_fuzzing')->default(true);
            $table->timestamp('optimized_at')->nullable();
            $table->integer('optimized_review_count')->nullable();
            $table->timestamps();
        });

        // Give every existing deck the defaults so nothing has to handle a null config.
        $now = now();
        $decks = DB::table('decks')->pluck('id');

        if ($decks->isNotEmpty()) {
            DB::table('deck_configs')->insert(
                $decks->map(fn (int $id): array => [
                    'deck_id' => $id,
                    'parameters' => json_encode(Parameters::DEFAULTS),
                    'desired_retention' => SchedulerConfig::DEFAULT_DESIRED_RETENTION,
                    'learning_steps' => json_encode(SchedulerConfig::DEFAULT_LEARNING_STEPS),
                    'relearning_steps' => json_encode(SchedulerConfig::DEFAULT_RELEARNING_STEPS),
                    'maximum_interval' => SchedulerConfig::DEFAULT_MAXIMUM_INTERVAL,
                    'enable_fuzzing' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all()
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deck_configs');
    }
};
