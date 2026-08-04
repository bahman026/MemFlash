<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Card;
use App\Models\StaticCard;

/**
 * SM-2 style review scheduling.
 *
 * This logic previously existed as four verbatim copies in StudyController and
 * StaticDeckController, which meant any tweak had to be applied in four places
 * or the personal-deck and static-deck schedules would silently diverge.
 */
class SpacedRepetitionService
{
    /**
     * Ease factor is never allowed below this, otherwise intervals collapse.
     */
    private const MIN_EASE_FACTOR = 1.3;

    /**
     * SM-2 starting ease factor, matching the static_cards column default.
     */
    private const DEFAULT_EASE_FACTOR = 2.5;

    /**
     * Apply a review rating to a card and persist the new schedule.
     *
     * @param  int  $quality  0 = Again, 1 = Hard, 2 = Good, 3 = Easy
     */
    public function review(Card | StaticCard $card, int $quality): void
    {
        // A single timestamp for the whole update. Carbon is mutable, so every
        // derived date must come from copy() -- the original code called
        // $now->addDay() and then reused $now for last_reviewed, which meant
        // last_reviewed was silently set to the *next due date* rather than now
        // (and in batch loops the drift compounded card after card).
        $reviewedAt = now();

        // `cards.interval` is nullable with no database default, and CardController
        // creates cards with only front/back -- so a manually added card arrives
        // here with interval NULL. Casting that to int yields 0, which would make
        // `interval * ease_factor` collapse to 0 and leave the card permanently due.
        // Same guard for ease_factor: a 0 ease factor zeroes every future interval.
        $currentInterval = max(1, (int) $card->interval);
        $currentEaseFactor = (float) ($card->ease_factor ?: self::DEFAULT_EASE_FACTOR);
        $currentRepetitions = (int) $card->repetitions;

        if ($currentEaseFactor < self::MIN_EASE_FACTOR) {
            $currentEaseFactor = self::DEFAULT_EASE_FACTOR;
        }

        if ($quality === 0) {
            // Again -- start the card over tomorrow and penalise the ease factor.
            $interval = 1;
            $repetitions = 0;
            $easeFactor = max(self::MIN_EASE_FACTOR, $currentEaseFactor - 0.2);
        } else {
            $interval = $currentInterval;
            $easeFactor = $currentEaseFactor;
            $repetitions = $currentRepetitions + 1;

            if ($quality === 1) {
                // Hard
                $interval = (int) max(1, $currentInterval * 1.2);
                $easeFactor = max(self::MIN_EASE_FACTOR, $currentEaseFactor - 0.15);
            } elseif ($quality === 2) {
                // Good
                $interval = match ($currentRepetitions) {
                    0 => 1,
                    1 => 6,
                    default => (int) ($currentInterval * $currentEaseFactor),
                };
            } elseif ($quality === 3) {
                // Easy
                $interval = match ($currentRepetitions) {
                    0 => 4,
                    1 => 10,
                    default => (int) ($currentInterval * $currentEaseFactor),
                };
                $easeFactor = $currentEaseFactor + 0.15;
            }
        }

        $card->update([
            'interval' => $interval,
            'repetitions' => $repetitions,
            'ease_factor' => $easeFactor,
            'revised_at' => $reviewedAt->copy()->addDays($interval),
            'last_reviewed' => $reviewedAt,
        ]);
    }
}
