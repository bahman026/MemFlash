<?php

declare(strict_types=1);

namespace App\Fsrs;

/**
 * Per-deck ("preset") scheduling configuration.
 *
 * Parameters are stored per deck, never globally -- different material produces
 * different memory behaviour, so one optimized set cannot serve every deck.
 */
final class SchedulerConfig
{
    public const DEFAULT_DESIRED_RETENTION = 0.90;

    public const MIN_DESIRED_RETENTION = 0.70;

    public const MAX_DESIRED_RETENTION = 0.97;

    public const DEFAULT_MAXIMUM_INTERVAL = 36500;

    /** Learning steps in seconds: 1 minute, then 10 minutes. */
    public const DEFAULT_LEARNING_STEPS = [60, 600];

    /** Relearning steps in seconds: 10 minutes. */
    public const DEFAULT_RELEARNING_STEPS = [600];

    /**
     * @param  list<int>  $learningSteps  seconds
     * @param  list<int>  $relearningSteps  seconds
     */
    public function __construct(
        public readonly Parameters $parameters = new Parameters,
        public readonly float $desiredRetention = self::DEFAULT_DESIRED_RETENTION,
        public readonly array $learningSteps = self::DEFAULT_LEARNING_STEPS,
        public readonly array $relearningSteps = self::DEFAULT_RELEARNING_STEPS,
        public readonly int $maximumInterval = self::DEFAULT_MAXIMUM_INTERVAL,
        public readonly bool $enableFuzzing = true,
        public readonly int $rolloverHour = 4,
        public readonly string $timezone = 'UTC',
    ) {}

    /**
     * Clamp the user-facing retention dial. Above 0.95 the review load grows
     * steeply for very little retention gain, so the range is capped rather than
     * silently allowing 0.99.
     */
    public static function clampDesiredRetention(float $value): float
    {
        return max(self::MIN_DESIRED_RETENTION, min(self::MAX_DESIRED_RETENTION, $value));
    }

    /**
     * @return list<int>
     */
    public function stepsFor(CardState $state): array
    {
        return $state === CardState::Relearning ? $this->relearningSteps : $this->learningSteps;
    }
}
