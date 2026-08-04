<?php

declare(strict_types=1);

namespace App\Models;

use App\Fsrs\Parameters;
use App\Fsrs\SchedulerConfig;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\UserStaticDeckSetting
 *
 * Per-user settings and scheduling preset for a shared static deck. Personal
 * decks keep their preset in deck_configs; static decks keep it here because the
 * schedule is per learner, not per deck.
 *
 * A null `parameters` means "use the FSRS-6 defaults", so an unoptimized deck
 * stores no weights at all.
 *
 * @property positive-int $id
 * @property positive-int $user_id
 * @property positive-int $static_deck_id
 * @property int $cards_per_day
 * @property list<float>|null $parameters
 * @property float $desired_retention
 * @property list<int>|null $learning_steps
 * @property list<int>|null $relearning_steps
 * @property int $maximum_interval
 * @property bool $enable_fuzzing
 * @property \Carbon\Carbon|null $optimized_at
 * @property int|null $optimized_review_count
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read StaticDeck $staticDeck
 * @property-read User $user
 */
class UserStaticDeckSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'static_deck_id',
        'cards_per_day',
        'parameters',
        'desired_retention',
        'learning_steps',
        'relearning_steps',
        'maximum_interval',
        'enable_fuzzing',
        'optimized_at',
        'optimized_review_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cards_per_day' => 'integer',
            'parameters' => 'array',
            'desired_retention' => 'float',
            'learning_steps' => 'array',
            'relearning_steps' => 'array',
            'maximum_interval' => 'integer',
            'enable_fuzzing' => 'boolean',
            'optimized_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The user this setting belongs to
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The static deck this setting belongs to
     *
     * @return BelongsTo<StaticDeck, $this>
     */
    public function staticDeck(): BelongsTo
    {
        return $this->belongsTo(StaticDeck::class);
    }

    public function hasBeenOptimized(): bool
    {
        return $this->optimized_at !== null;
    }

    public function toSchedulerConfig(User $user): SchedulerConfig
    {
        return new SchedulerConfig(
            parameters: new Parameters($this->parameters ?: null),
            desiredRetention: SchedulerConfig::clampDesiredRetention(
                $this->desired_retention ?? SchedulerConfig::DEFAULT_DESIRED_RETENTION
            ),
            learningSteps: array_map('intval', $this->learning_steps ?: SchedulerConfig::DEFAULT_LEARNING_STEPS),
            relearningSteps: array_map('intval', $this->relearning_steps ?: SchedulerConfig::DEFAULT_RELEARNING_STEPS),
            maximumInterval: $this->maximum_interval ?? SchedulerConfig::DEFAULT_MAXIMUM_INTERVAL,
            enableFuzzing: (bool) ($this->enable_fuzzing ?? true),
            rolloverHour: (int) $user->rollover_hour,
            timezone: $user->timezone ?: 'UTC',
        );
    }
}
