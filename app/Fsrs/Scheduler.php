<?php

declare(strict_types=1);

namespace App\Fsrs;

use App\Fsrs\Fuzz\FuzzSource;
use App\Fsrs\Fuzz\RandomFuzzSource;
use DateTimeImmutable;
use DateTimeZone;

/**
 * The FSRS-6 scheduler: the memory model (Fsrs) wrapped in the state machine
 * that handles sub-day learning and relearning steps.
 *
 * Pure by construction. No database, no `now()` inside -- the timestamp is passed
 * in, and randomness arrives through FuzzSource. That is what makes it testable
 * against the reference vectors with no I/O.
 */
final class Scheduler
{
    /**
     * Fuzz applies only in the Review state and only from 2.5 days up.
     *
     * @var list<array{start: float, end: float, factor: float}>
     */
    private const FUZZ_RANGES = [
        ['start' => 2.5, 'end' => 7.0, 'factor' => 0.15],
        ['start' => 7.0, 'end' => 20.0, 'factor' => 0.10],
        ['start' => 20.0, 'end' => INF, 'factor' => 0.05],
    ];

    private const SECONDS_PER_DAY = 86400;

    public function __construct(
        private readonly SchedulerConfig $config = new SchedulerConfig,
        private readonly Fsrs $fsrs = new Fsrs,
        private readonly FuzzSource $fuzz = new RandomFuzzSource,
    ) {}

    public function config(): SchedulerConfig
    {
        return $this->config;
    }

    public function fsrs(): Fsrs
    {
        return $this->fsrs;
    }

    /**
     * Grade a card.
     *
     * Order matters and is enforced here:
     *   1. observe elapsed_days and R BEFORE mutating anything
     *   2. update D (F5) BEFORE computing S, because F6 and F7 both take the new D
     *   3. transition state and compute the due date
     *   4. fuzz, Review state only
     */
    public function review(CardSnapshot $card, Rating $rating, DateTimeImmutable $now): ReviewOutcome
    {
        $stateBefore = $card->state;

        // --- 1. observe, before any mutation ---
        if ($card->isNew()) {
            $elapsedDays = 0;
            $retrievability = 1.0;
        } else {
            $elapsedDays = $this->dayDifference($card->lastReview, $now);
            $retrievability = $this->fsrs->retrievability((float) $elapsedDays, (float) $card->stability);
        }

        // --- 2. update memory state ---
        if ($card->isNew()) {
            $stability = $this->fsrs->initialStability($rating);
            $difficulty = $this->fsrs->initialDifficulty($rating);
        } else {
            $difficulty = $this->fsrs->nextDifficulty((float) $card->difficulty, $rating);

            if ($elapsedDays < 1) {
                $stability = $this->fsrs->stabilitySameDay((float) $card->stability, $rating);
            } elseif ($rating->isLapse()) {
                $stability = $this->fsrs->stabilityAfterLapse($difficulty, (float) $card->stability, $retrievability);
            } else {
                $stability = $this->fsrs->stabilityAfterRecall($difficulty, (float) $card->stability, $retrievability, $rating);
            }
        }

        // --- 3. counters ---
        $reps = $card->reps + 1;
        $lapses = $card->lapses + ($rating->isLapse() ? 1 : 0);

        // --- 4. transition ---
        [$state, $step, $seconds] = $this->transition($card, $rating, $stability);

        // --- 5. fuzz, Review state only ---
        $scheduledDays = 0;
        if ($state === CardState::Review) {
            $days = (int) round($seconds / self::SECONDS_PER_DAY);

            if ($this->config->enableFuzzing) {
                $days = $this->applyFuzz($days, $elapsedDays);
            }

            $scheduledDays = $days;
            $seconds = $days * self::SECONDS_PER_DAY;
        }

        return new ReviewOutcome(
            state: $state,
            step: $step,
            stability: $stability,
            difficulty: $difficulty,
            due: $now->modify("+{$seconds} seconds"),
            lastReview: $now,
            reps: $reps,
            lapses: $lapses,
            rating: $rating,
            stateBefore: $stateBefore,
            elapsedDays: $elapsedDays,
            retrievabilityBefore: $retrievability,
            scheduledDays: $scheduledDays,
            scheduledSeconds: $seconds,
        );
    }

    /**
     * Preview what each of the four ratings would schedule, so the answer buttons
     * can show intervals without a second round trip.
     *
     * @return array<int, ReviewOutcome> keyed by Rating value 1..4
     */
    public function preview(CardSnapshot $card, DateTimeImmutable $now): array
    {
        $out = [];
        foreach (Rating::cases() as $rating) {
            $out[$rating->value] = $this->review($card, $rating, $now);
        }

        return $out;
    }

    /**
     * The interval a card in the Review state would get for its stability.
     */
    public function intervalDays(float $stability): int
    {
        return $this->fsrs->intervalDays(
            $stability,
            $this->config->desiredRetention,
            $this->config->maximumInterval
        );
    }

    /**
     * Part 2.3. Returns [state, step, seconds until due].
     *
     * The Hard-on-first-step rules are a scheduling convention layered on top of
     * the memory model, not part of it. They match the reference implementation
     * and are kept for compatibility.
     *
     * @return array{0: CardState, 1: int|null, 2: int}
     */
    private function transition(CardSnapshot $card, Rating $rating, float $stability): array
    {
        $graduate = fn (): array => [
            CardState::Review,
            null,
            $this->intervalDays($stability) * self::SECONDS_PER_DAY,
        ];

        // --- From New ---
        if ($card->isNew()) {
            if ($this->config->learningSteps === []) {
                return $graduate();
            }

            return [CardState::Learning, 0, $this->config->learningSteps[0]];
        }

        // --- From Review ---
        if ($card->state === CardState::Review) {
            if ($rating->isLapse() && $this->config->relearningSteps !== []) {
                return [CardState::Relearning, 0, $this->config->relearningSteps[0]];
            }

            return $graduate();
        }

        // --- From Learning or Relearning ---
        $steps = $this->config->stepsFor($card->state);
        $step = $card->step ?? 0;

        if ($steps === [] || $step >= count($steps)) {
            return $graduate();
        }

        return match ($rating) {
            Rating::Again => [$card->state, 0, $steps[0]],

            Rating::Hard => [
                $card->state,
                $step,
                match (true) {
                    $step === 0 && count($steps) === 1 => (int) round($steps[0] * 1.5),
                    $step === 0 => (int) round(($steps[0] + $steps[1]) / 2),
                    default => $steps[$step],
                },
            ],

            Rating::Good => $step + 1 >= count($steps)
                ? $graduate()
                : [$card->state, $step + 1, $steps[$step + 1]],

            Rating::Easy => $graduate(),
        };
    }

    /**
     * Section 1.12. Spreads cards that were introduced together so they do not
     * stay clumped forever.
     */
    private function applyFuzz(int $interval, int $elapsedDays): int
    {
        if ($interval < 2.5) {
            return $interval;
        }

        $delta = 1.0;
        foreach (self::FUZZ_RANGES as $range) {
            $delta += $range['factor'] * max(min((float) $interval, $range['end']) - $range['start'], 0.0);
        }

        $min = max(2, (int) round($interval - $delta));
        $max = min((int) round($interval + $delta), $this->config->maximumInterval);

        // Never let fuzz schedule a card sooner than it has already been waiting.
        if ($interval > $elapsedDays) {
            $min = max($min, $elapsedDays + 1);
        }

        $min = min($min, $max);

        return min(
            (int) round($this->fuzz->next() * ($max - $min + 1) + $min),
            $this->config->maximumInterval
        );
    }

    /**
     * Whole days between two instants, counted in rollover boundaries crossed.
     *
     * Anki rolls the day over at 4 a.m. local time, not midnight, so a review at
     * 2 a.m. still belongs to the previous day. This decides whether F8 (same-day)
     * or F6/F7 runs, so a naive 24-hour difference would change scheduling.
     */
    public function dayDifference(DateTimeImmutable $from, DateTimeImmutable $to): int
    {
        $tz = new DateTimeZone($this->config->timezone);
        $hours = $this->config->rolloverHour;

        $a = $from->setTimezone($tz)->modify("-{$hours} hours")->setTime(0, 0);
        $b = $to->setTimezone($tz)->modify("-{$hours} hours")->setTime(0, 0);

        $diff = $a->diff($b);

        return (int) $diff->days * ($diff->invert === 1 ? -1 : 1);
    }
}
