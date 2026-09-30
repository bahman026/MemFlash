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
     *   2. compute S (F6/F7/F8) from the PRE-review D, then update D (F5) --
     *      the order of py-fsrs and fsrs-rs, which fitted the default weights
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
            // isNew() only rules out NULL stability/difficulty; it does not rule out
            // an out-of-range persisted value (e.g. 0.0). Every formula that WRITES
            // stability floors it at S_MIN, but nothing previously floored it on
            // READ, so a stray 0.0 -- from a hand-edited row, a bad import, or a
            // future bug -- reached `stability ** (-w9)` and produced NAN, which
            // then propagates forever since every later review starts from it.
            // Confirmed: stability=0.0 (not null) yielded NAN with a "power of base
            // 0 and negative exponent" deprecation notice.
            $priorStability = max(Parameters::S_MIN, (float) $card->stability);
            $priorDifficulty = min(Parameters::D_MAX, max(Parameters::D_MIN, (float) $card->difficulty));

            // Never negative. A review timestamped before the card's last review
            // (a stale offline replay, a device clock running behind) would
            // otherwise give R > 1, or NaN once the curve's base goes negative,
            // and that NaN is written to the log and carried forward.
            $elapsedDays = max(0, $this->dayDifference($card->lastReview, $now));
            $retrievability = $this->fsrs->retrievability((float) $elapsedDays, $priorStability);
        }

        // --- 2. update memory state ---
        if ($card->isNew()) {
            $stability = $this->fsrs->initialStability($rating);
            $difficulty = $this->fsrs->initialDifficulty($rating);
        } else {
            // Stability from the difficulty the card had BEFORE this review, then
            // update difficulty -- the order of py-fsrs, fsrs-rs and the optimizer
            // that fitted the default weights. Feeding the new D into stability
            // (as before 2026-09-30) shortened Hard intervals by ~15% and
            // lengthened Easy ones by ~23% (S=10, D=5 after 10 days: Hard 20 vs
            // 23 days, Easy 63 vs 51).
            if ($elapsedDays < 1) {
                $stability = $this->fsrs->stabilitySameDay($priorStability, $rating);
            } elseif ($rating->isLapse()) {
                $stability = $this->fsrs->stabilityAfterLapse($priorDifficulty, $priorStability, $retrievability);
            } else {
                $stability = $this->fsrs->stabilityAfterRecall($priorDifficulty, $priorStability, $retrievability, $rating);
            }

            $difficulty = $this->fsrs->nextDifficulty($priorDifficulty, $rating);
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
        // A new card enters Learning at step 0, and its first rating then moves it
        // along the steps like any learning answer: Easy graduates at once, Good
        // goes to the next step, Hard waits between the first two. This is the
        // reference behaviour (py-fsrs). Returning step 0 for every rating made the
        // first answer meaningless and labelled all four buttons "1m".
        if ($card->isNew()) {
            return $this->stepTransition(CardState::Learning, 0, $this->config->learningSteps, $rating, $graduate);
        }

        // --- From Review ---
        if ($card->state === CardState::Review) {
            if ($rating->isLapse() && $this->config->relearningSteps !== []) {
                return [CardState::Relearning, 0, $this->config->relearningSteps[0]];
            }

            return $graduate();
        }

        // --- From Learning or Relearning ---
        return $this->stepTransition(
            $card->state,
            $card->step ?? 0,
            $this->config->stepsFor($card->state),
            $rating,
            $graduate,
        );
    }

    /**
     * One answer on a learning or relearning step.
     *
     * @param  list<int>  $steps  seconds per step
     * @param  \Closure(): array{0: CardState, 1: int|null, 2: int}  $graduate
     * @return array{0: CardState, 1: int|null, 2: int}
     */
    private function stepTransition(CardState $state, int $step, array $steps, Rating $rating, \Closure $graduate): array
    {
        if ($steps === [] || $step >= count($steps)) {
            return $graduate();
        }

        return match ($rating) {
            Rating::Again => [$state, 0, $steps[0]],

            Rating::Hard => [
                $state,
                $step,
                match (true) {
                    $step === 0 && count($steps) === 1 => (int) round($steps[0] * 1.5),
                    $step === 0 => (int) round(($steps[0] + $steps[1]) / 2),
                    default => $steps[$step],
                },
            ],

            Rating::Good => $step + 1 >= count($steps)
                ? $graduate()
                : [$state, $step + 1, $steps[$step + 1]],

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
