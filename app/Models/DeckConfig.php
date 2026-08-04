<?php

declare(strict_types=1);

namespace App\Models;

use App\Fsrs\Parameters;
use App\Fsrs\SchedulerConfig;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\DeckConfig
 *
 * The scheduling preset for one personal deck.
 *
 * @property positive-int $id
 * @property positive-int $deck_id
 * @property list<float> $parameters
 * @property float $desired_retention
 * @property list<int> $learning_steps
 * @property list<int> $relearning_steps
 * @property int $maximum_interval
 * @property bool $enable_fuzzing
 * @property Carbon|null $optimized_at
 * @property int|null $optimized_review_count
 * @property-read Deck $deck
 */
class DeckConfig extends Model
{
    protected $fillable = [
        'deck_id',
        'parameters',
        'desired_retention',
        'learning_steps',
        'relearning_steps',
        'maximum_interval',
        'enable_fuzzing',
        'optimized_at',
        'optimized_review_count',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'desired_retention' => 'float',
            'learning_steps' => 'array',
            'relearning_steps' => 'array',
            'maximum_interval' => 'integer',
            'enable_fuzzing' => 'boolean',
            'optimized_at' => 'datetime',
            'optimized_review_count' => 'integer',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'parameters' => Parameters::DEFAULTS,
            'desired_retention' => SchedulerConfig::DEFAULT_DESIRED_RETENTION,
            'learning_steps' => SchedulerConfig::DEFAULT_LEARNING_STEPS,
            'relearning_steps' => SchedulerConfig::DEFAULT_RELEARNING_STEPS,
            'maximum_interval' => SchedulerConfig::DEFAULT_MAXIMUM_INTERVAL,
            'enable_fuzzing' => true,
        ];
    }

    /**
     * @return BelongsTo<Deck, $this>
     */
    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    /**
     * Has this deck logged enough reviews to be worth optimizing?
     */
    public function hasBeenOptimized(): bool
    {
        return $this->optimized_at !== null;
    }

    /**
     * Turn the stored row into the value object the scheduler consumes.
     *
     * The rollover hour and timezone come from the user, not the deck, since they
     * describe when that person's day starts.
     */
    public function toSchedulerConfig(User $user): SchedulerConfig
    {
        return new SchedulerConfig(
            parameters: new Parameters($this->parameters ?: null),
            desiredRetention: SchedulerConfig::clampDesiredRetention($this->desired_retention),
            learningSteps: array_map('intval', $this->learning_steps ?: SchedulerConfig::DEFAULT_LEARNING_STEPS),
            relearningSteps: array_map('intval', $this->relearning_steps ?: SchedulerConfig::DEFAULT_RELEARNING_STEPS),
            maximumInterval: $this->maximum_interval,
            enableFuzzing: $this->enable_fuzzing,
            rolloverHour: (int) $user->rollover_hour,
            timezone: $user->timezone ?: 'UTC',
        );
    }
}
