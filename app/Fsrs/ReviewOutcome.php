<?php

declare(strict_types=1);

namespace App\Fsrs;

use DateTimeImmutable;

/**
 * The scheduler's output: the card's new memory state plus everything the
 * mandatory review_logs row needs.
 *
 * The log is not optional -- the optimizer trains on it, so a review that is not
 * logged can never contribute to personalising the parameters.
 */
final class ReviewOutcome
{
    public function __construct(
        public readonly CardState $state,
        public readonly ?int $step,
        public readonly float $stability,
        public readonly float $difficulty,
        public readonly DateTimeImmutable $due,
        public readonly DateTimeImmutable $lastReview,
        public readonly int $reps,
        public readonly int $lapses,
        // --- log fields, observed before the update ---
        public readonly Rating $rating,
        public readonly CardState $stateBefore,
        public readonly int $elapsedDays,
        public readonly float $retrievabilityBefore,
        /** Whole days until due; 0 for sub-day learning steps. */
        public readonly int $scheduledDays,
        /** Exact seconds until due, for sub-day steps. */
        public readonly int $scheduledSeconds,
    ) {}

    /**
     * Did this review put the card on a day-scale schedule?
     */
    public function isDayScale(): bool
    {
        return $this->state === CardState::Review;
    }

    /**
     * @return array<string, mixed>
     */
    public function toLogArray(): array
    {
        return [
            'rating' => $this->rating->value,
            'state_before' => $this->stateBefore->value,
            'elapsed_days' => $this->elapsedDays,
            'retrievability_before' => $this->retrievabilityBefore,
            'stability_after' => $this->stability,
            'difficulty_after' => $this->difficulty,
            'scheduled_days' => $this->scheduledDays,
            'reviewed_at' => $this->lastReview,
        ];
    }
}
