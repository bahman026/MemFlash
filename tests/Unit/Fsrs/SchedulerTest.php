<?php

declare(strict_types=1);

use App\Fsrs\CardSnapshot;
use App\Fsrs\CardState;
use App\Fsrs\Fsrs;
use App\Fsrs\Fuzz\FixedFuzzSource;
use App\Fsrs\Rating;
use App\Fsrs\Scheduler;
use App\Fsrs\SchedulerConfig;

/**
 * Part 2 -- the scheduler state machine. Fuzzing off unless a test enables it.
 */
function scheduler(array $overrides = [], ?float $fuzzValue = null): Scheduler
{
    $config = new SchedulerConfig(
        desiredRetention: $overrides['desiredRetention'] ?? 0.90,
        learningSteps: $overrides['learningSteps'] ?? SchedulerConfig::DEFAULT_LEARNING_STEPS,
        relearningSteps: $overrides['relearningSteps'] ?? SchedulerConfig::DEFAULT_RELEARNING_STEPS,
        maximumInterval: $overrides['maximumInterval'] ?? 36500,
        enableFuzzing: $overrides['enableFuzzing'] ?? false,
        rolloverHour: $overrides['rolloverHour'] ?? 4,
        timezone: $overrides['timezone'] ?? 'UTC',
    );

    return new Scheduler($config, new Fsrs, new FixedFuzzSource($fuzzValue ?? 0.0));
}

function at(string $iso): DateTimeImmutable
{
    return new DateTimeImmutable($iso, new DateTimeZone('UTC'));
}

// -------------------------------------------------------------------------
// New -> Learning
// -------------------------------------------------------------------------

it('records a new card entering learning with its first review', function (): void {
    $out = scheduler()->review(CardSnapshot::new(), Rating::Again, at('2026-01-01 10:00:00'));

    expect($out->state)->toBe(CardState::Learning)
        ->and($out->step)->toBe(0)
        ->and($out->scheduledSeconds)->toBe(60)
        ->and($out->stateBefore)->toBe(CardState::New)
        ->and($out->elapsedDays)->toBe(0)
        ->and($out->retrievabilityBefore)->toBe(1.0)
        ->and($out->reps)->toBe(1);
});

// The first rating on a new card moves it along the learning steps exactly as it
// would from step 0 (py-fsrs). It used to be ignored: every rating gave step 0
// in 60 seconds, so Easy on a word you already knew still asked again in a minute.
it('applies the first rating on a new card to the learning steps', function (Rating $rating, CardState $state, ?int $step, int $seconds): void {
    $out = scheduler()->review(CardSnapshot::new(), $rating, at('2026-01-01 10:00:00'));

    expect($out->state)->toBe($state)
        ->and($out->step)->toBe($step)
        ->and($out->scheduledSeconds)->toBe($seconds);
})->with([
    'again' => [Rating::Again, CardState::Learning, 0, 60],
    'hard' => [Rating::Hard, CardState::Learning, 0, 330],   // (60 + 600) / 2
    'good' => [Rating::Good, CardState::Learning, 1, 600],
    'easy' => [Rating::Easy, CardState::Review, null, 8 * 86400], // S0(Easy) = 8.2956
]);

it('graduates a new card straight to review when there are no learning steps', function (): void {
    $out = scheduler(['learningSteps' => []])->review(CardSnapshot::new(), Rating::Good, at('2026-01-01 10:00:00'));

    expect($out->state)->toBe(CardState::Review)
        ->and($out->step)->toBeNull()
        ->and($out->scheduledDays)->toBe(2); // S0(Good) = 2.3065 -> 2 days
});

it('counts a lapse on a new card', function (): void {
    $out = scheduler()->review(CardSnapshot::new(), Rating::Again, at('2026-01-01 10:00:00'));

    expect($out->lapses)->toBe(1)
        ->and(round($out->stability, 4))->toBe(0.2120)
        ->and(round($out->difficulty, 4))->toBe(6.4133);
});

// -------------------------------------------------------------------------
// Learning steps
// -------------------------------------------------------------------------

it('walks Good through the learning steps then graduates', function (): void {
    $s = scheduler();

    $card = new CardSnapshot(CardState::Learning, 0, 2.3065, 2.1181, at('2026-01-01 10:00:00'), 1, 0);
    $out = $s->review($card, Rating::Good, at('2026-01-01 10:01:00'));

    expect($out->state)->toBe(CardState::Learning)
        ->and($out->step)->toBe(1)
        ->and($out->scheduledSeconds)->toBe(600);

    // last step + Good -> Review
    $card = new CardSnapshot(CardState::Learning, 1, $out->stability, $out->difficulty, at('2026-01-01 10:01:00'), 2, 0);
    $out = $s->review($card, Rating::Good, at('2026-01-01 10:11:00'));

    expect($out->state)->toBe(CardState::Review)
        ->and($out->step)->toBeNull()
        ->and($out->scheduledDays)->toBeGreaterThanOrEqual(1);
});

it('resets Again to the first learning step', function (): void {
    $card = new CardSnapshot(CardState::Learning, 1, 5.0, 5.0, at('2026-01-01 10:00:00'), 2, 0);
    $out = scheduler()->review($card, Rating::Again, at('2026-01-01 10:05:00'));

    expect($out->state)->toBe(CardState::Learning)
        ->and($out->step)->toBe(0)
        ->and($out->scheduledSeconds)->toBe(60);
});

it('averages the first two steps for Hard on step 0', function (): void {
    $card = new CardSnapshot(CardState::Learning, 0, 2.3065, 2.1181, at('2026-01-01 10:00:00'), 1, 0);
    $out = scheduler()->review($card, Rating::Hard, at('2026-01-01 10:00:30'));

    // (60 + 600) / 2
    expect($out->scheduledSeconds)->toBe(330)
        ->and($out->step)->toBe(0);
});

it('multiplies the only step by 1.5 for Hard when there is a single step', function (): void {
    $card = new CardSnapshot(CardState::Learning, 0, 2.3065, 2.1181, at('2026-01-01 10:00:00'), 1, 0);
    $out = scheduler(['learningSteps' => [60]])->review($card, Rating::Hard, at('2026-01-01 10:00:30'));

    expect($out->scheduledSeconds)->toBe(90);
});

it('stays on the same step for Hard beyond step 0', function (): void {
    $card = new CardSnapshot(CardState::Learning, 1, 2.3065, 2.1181, at('2026-01-01 10:00:00'), 2, 0);
    $out = scheduler()->review($card, Rating::Hard, at('2026-01-01 10:05:00'));

    expect($out->step)->toBe(1)
        ->and($out->scheduledSeconds)->toBe(600);
});

it('graduates immediately on Easy', function (): void {
    $card = new CardSnapshot(CardState::Learning, 0, 2.3065, 2.1181, at('2026-01-01 10:00:00'), 1, 0);
    $out = scheduler()->review($card, Rating::Easy, at('2026-01-01 10:00:30'));

    expect($out->state)->toBe(CardState::Review)
        ->and($out->step)->toBeNull();
});

// -------------------------------------------------------------------------
// Review -> Relearning
// -------------------------------------------------------------------------

it('sends a lapsed review card into relearning', function (): void {
    $card = new CardSnapshot(CardState::Review, null, 10.0, 5.0, at('2026-01-01 10:00:00'), 5, 1);
    $out = scheduler()->review($card, Rating::Again, at('2026-01-11 10:00:00'));

    expect($out->state)->toBe(CardState::Relearning)
        ->and($out->step)->toBe(0)
        ->and($out->scheduledSeconds)->toBe(600)
        ->and($out->lapses)->toBe(2)
        ->and($out->elapsedDays)->toBe(10)
        ->and(round($out->retrievabilityBefore, 10))->toBe(0.9)
        ->and(round($out->stability, 4))->toBe(1.3489)   // vector 4.4 Again
        ->and(round($out->difficulty, 4))->toBe(8.3475);
});

it('keeps a lapsed card in review when there are no relearning steps', function (): void {
    $card = new CardSnapshot(CardState::Review, null, 10.0, 5.0, at('2026-01-01 10:00:00'), 5, 0);
    $out = scheduler(['relearningSteps' => []])->review($card, Rating::Again, at('2026-01-11 10:00:00'));

    expect($out->state)->toBe(CardState::Review)
        ->and($out->scheduledDays)->toBe(1);
});

it('keeps successful review cards in review with the F2 interval', function (Rating $rating, int $expected): void {
    $card = new CardSnapshot(CardState::Review, null, 10.0, 5.0, at('2026-01-01 10:00:00'), 5, 0);
    $out = scheduler()->review($card, $rating, at('2026-01-11 10:00:00'));

    expect($out->state)->toBe(CardState::Review)
        ->and($out->scheduledDays)->toBe($expected);
})->with([
    'Hard' => [Rating::Hard, 20],
    'Good' => [Rating::Good, 32],
    'Easy' => [Rating::Easy, 63],
]);

it('graduates from relearning on Good', function (): void {
    $card = new CardSnapshot(CardState::Relearning, 0, 1.3489, 8.3475, at('2026-01-01 10:00:00'), 6, 1);
    $out = scheduler()->review($card, Rating::Good, at('2026-01-01 10:10:00'));

    expect($out->state)->toBe(CardState::Review)
        ->and($out->step)->toBeNull();
});

// -------------------------------------------------------------------------
// Same-day vs day-scale, and the rollover hour
// -------------------------------------------------------------------------

it('uses the same-day formula inside one rollover day', function (): void {
    $card = new CardSnapshot(CardState::Review, null, 2.0, 5.0, at('2026-01-01 10:00:00'), 3, 0);
    $out = scheduler()->review($card, Rating::Good, at('2026-01-01 22:00:00'));

    expect($out->elapsedDays)->toBe(0)
        ->and(round($out->stability, 6))->toBe(2.007749); // vector 4.5 Good
});

it('treats a review before the 4am rollover as the same day', function (): void {
    $s = scheduler(['rolloverHour' => 4]);

    // 23:00 Jan 1 -> 02:00 Jan 2 is a calendar day apart but the same Anki day.
    expect($s->dayDifference(at('2026-01-01 23:00:00'), at('2026-01-02 02:00:00')))->toBe(0)
        ->and($s->dayDifference(at('2026-01-01 23:00:00'), at('2026-01-02 05:00:00')))->toBe(1);
});

it('counts day differences across the rollover, not in 24h blocks', function (): void {
    $s = scheduler(['rolloverHour' => 4]);

    // Only 3 hours apart, but they straddle the 4am boundary.
    expect($s->dayDifference(at('2026-01-01 03:00:00'), at('2026-01-01 06:00:00')))->toBe(1);
});

it('honours the configured timezone for day boundaries', function (): void {
    $s = scheduler(['rolloverHour' => 4, 'timezone' => 'Asia/Tehran']);

    expect($s->dayDifference(at('2026-01-01 00:00:00'), at('2026-01-01 12:00:00')))->toBe(1);
});

// -------------------------------------------------------------------------
// Fuzz
// -------------------------------------------------------------------------

it('does not fuzz when fuzzing is disabled', function (): void {
    $card = new CardSnapshot(CardState::Review, null, 10.0, 5.0, at('2026-01-01 10:00:00'), 5, 0);
    $out = scheduler(['enableFuzzing' => false])->review($card, Rating::Good, at('2026-01-11 10:00:00'));

    expect($out->scheduledDays)->toBe(32);
});

it('keeps fuzzed intervals inside the documented window', function (): void {
    $card = new CardSnapshot(CardState::Review, null, 10.0, 5.0, at('2026-01-01 10:00:00'), 5, 0);

    // interval 32 -> delta = 1 + .15*4.5 + .10*13 + .05*12 = 3.575
    //   min = max(2, round(32 - 3.575)) = 28
    //   max = min(round(32 + 3.575), 36500) = 36
    $low = scheduler(['enableFuzzing' => true], 0.0)->review($card, Rating::Good, at('2026-01-11 10:00:00'));
    $mid = scheduler(['enableFuzzing' => true], 0.5)->review($card, Rating::Good, at('2026-01-11 10:00:00'));
    $high = scheduler(['enableFuzzing' => true], 0.9)->review($card, Rating::Good, at('2026-01-11 10:00:00'));

    expect($low->scheduledDays)->toBe(28)
        ->and($mid->scheduledDays)->toBe(33)
        ->and($high->scheduledDays)->toBe(36)
        ->and($low->scheduledDays)->toBeLessThan(32)
        ->and($high->scheduledDays)->toBeGreaterThan(32);
});

it('can overshoot the window by a day as random approaches 1', function (): void {
    // round(r * (max - min + 1) + min) reaches max + 1 as r -> 1. This is a quirk
    // of the reference fuzz formula, kept deliberately for compatibility; only the
    // maximum_interval cap is guaranteed.
    $card = new CardSnapshot(CardState::Review, null, 10.0, 5.0, at('2026-01-01 10:00:00'), 5, 0);
    $out = scheduler(['enableFuzzing' => true], 0.999999)->review($card, Rating::Good, at('2026-01-11 10:00:00'));

    expect($out->scheduledDays)->toBe(37);
});

it('never fuzzes intervals below 2.5 days', function (): void {
    // S0(Good) = 2.3065 -> a 2 day interval, under the 2.5 day fuzz threshold.
    $out = scheduler(['learningSteps' => [], 'enableFuzzing' => true], 0.999999)
        ->review(CardSnapshot::new(), Rating::Good, at('2026-01-01 10:00:00'));

    expect($out->scheduledDays)->toBe(2);
});

it('never fuzzes a card sooner than it has already waited', function (): void {
    $card = new CardSnapshot(CardState::Review, null, 30.0, 5.0, at('2026-01-01 10:00:00'), 5, 0);
    // Reviewed well before due, so elapsed is small; the low end must still respect it.
    $out = scheduler(['enableFuzzing' => true], 0.0)->review($card, Rating::Good, at('2026-01-06 10:00:00'));

    expect($out->scheduledDays)->toBeGreaterThan(5);
});

it('respects the maximum interval', function (): void {
    $card = new CardSnapshot(CardState::Review, null, 100000.0, 1.0, at('2026-01-01 10:00:00'), 50, 0);
    $out = scheduler(['maximumInterval' => 365, 'enableFuzzing' => true], 0.999999)
        ->review($card, Rating::Easy, at('2027-01-01 10:00:00'));

    expect($out->scheduledDays)->toBeLessThanOrEqual(365);
});

// -------------------------------------------------------------------------
// Preview and purity
// -------------------------------------------------------------------------

it('previews all four ratings without mutating the card', function (): void {
    $card = new CardSnapshot(CardState::Review, null, 10.0, 5.0, at('2026-01-01 10:00:00'), 5, 0);
    $previews = scheduler()->preview($card, at('2026-01-11 10:00:00'));

    expect($previews)->toHaveCount(4)
        ->and($previews[Rating::Hard->value]->scheduledDays)->toBe(20)
        ->and($previews[Rating::Good->value]->scheduledDays)->toBe(32)
        ->and($previews[Rating::Easy->value]->scheduledDays)->toBe(63)
        // the snapshot is readonly, so it cannot have changed
        ->and($card->stability)->toBe(10.0)
        ->and($card->reps)->toBe(5);
});

it('never persists retrievability on the outcome', function (): void {
    $out = scheduler()->review(CardSnapshot::new(), Rating::Good, at('2026-01-01 10:00:00'));

    // retrievability appears only as the pre-review observation for the log
    expect($out->toLogArray())->toHaveKey('retrievability_before')
        ->and(array_keys(get_object_vars($out)))->not->toContain('retrievability');
});
