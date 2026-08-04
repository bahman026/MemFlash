<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Fsrs\CardSnapshot;
use App\Fsrs\CardState;
use App\Fsrs\ReviewOutcome;
use Illuminate\Support\Carbon;

/**
 * Shared behaviour for rows that carry FSRS memory state.
 *
 * Two very different tables hold it: `cards` for personal decks, and
 * `user_static_card_states` for shared curriculum cards, where the state is
 * per user rather than per card. Keeping the conversion in one place means the
 * scheduler only ever sees a CardSnapshot and never an Eloquent model.
 *
 * Retrievability is absent by design. It is derived from stability and elapsed
 * time on every read, never stored.
 *
 * @property CardState $state
 * @property int|null $step
 * @property float|null $stability
 * @property float|null $difficulty
 * @property \Illuminate\Support\Carbon|null $due
 * @property \Illuminate\Support\Carbon|null $last_review
 * @property int $reps
 * @property int $lapses
 */
trait HasFsrsMemory
{
    /**
     * The FSRS columns every consumer of this trait must have.
     *
     * @return list<string>
     */
    public static function fsrsColumns(): array
    {
        return ['state', 'step', 'stability', 'difficulty', 'due', 'last_review', 'reps', 'lapses'];
    }

    /**
     * @return array<string, string>
     */
    protected function fsrsCasts(): array
    {
        return [
            'state' => CardState::class,
            'step' => 'integer',
            'stability' => 'float',
            'difficulty' => 'float',
            'due' => 'datetime',
            'last_review' => 'datetime',
            'reps' => 'integer',
            'lapses' => 'integer',
            'suspended' => 'boolean',
        ];
    }

    /**
     * Hand the scheduler a plain, immutable view of this row.
     */
    public function toSnapshot(): CardSnapshot
    {
        return new CardSnapshot(
            state: $this->state ?? CardState::New,
            step: $this->step,
            stability: $this->stability,
            difficulty: $this->difficulty,
            lastReview: $this->last_review?->toDateTimeImmutable(),
            reps: (int) $this->reps,
            lapses: (int) $this->lapses,
        );
    }

    /**
     * Write a scheduling result back. Does not save.
     */
    public function applyOutcome(ReviewOutcome $outcome): static
    {
        $this->state = $outcome->state;
        $this->step = $outcome->step;
        $this->stability = $outcome->stability;
        $this->difficulty = $outcome->difficulty;
        // The scheduler works in immutable time; the model columns are Carbon.
        $this->due = Carbon::instance($outcome->due);
        $this->last_review = Carbon::instance($outcome->lastReview);
        $this->reps = $outcome->reps;
        $this->lapses = $outcome->lapses;

        return $this;
    }

    /**
     * A card with no due date has never been scheduled, so it is due.
     */
    public function isDue(): bool
    {
        return $this->due === null || $this->due->isPast();
    }

    /**
     * Probability of recalling this card right now, always derived.
     */
    public function retrievability(?\DateTimeInterface $at = null): float
    {
        if ($this->stability === null || $this->last_review === null) {
            return 1.0;
        }

        $scheduler = app(\App\Fsrs\Fsrs::class);
        $elapsed = max(0, $this->last_review->diffInDays($at ?? now(), false));

        return $scheduler->retrievability((float) $elapsed, (float) $this->stability);
    }

    /**
     * Clear all memory of this card, returning it to New.
     *
     * @return array<string, mixed>
     */
    public static function forgottenState(): array
    {
        return [
            'state' => CardState::New,
            'step' => null,
            'stability' => null,
            'difficulty' => null,
            'due' => now(),
            'last_review' => null,
            'reps' => 0,
            'lapses' => 0,
        ];
    }

    public function forget(): void
    {
        $this->forceFill(static::forgottenState())->save();
    }
}
