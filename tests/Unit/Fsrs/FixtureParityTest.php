<?php

declare(strict_types=1);

use App\Fsrs\Fsrs;
use App\Fsrs\Parameters;
use App\Fsrs\Rating;

/**
 * The PHP half of the offline parity contract.
 *
 * Reads tests/fixtures/fsrs-vectors.json -- the same file that
 * resources/js/fsrs/fsrs.test.mjs reads. The server schedules reviews and the
 * offline mirror labels the answer buttons, so if the two implementations
 * disagree the user sees one interval and gets another. Pinning both to one
 * fixture means a divergence fails a suite instead of shipping.
 *
 * FsrsTest.php already covers these numbers inline; this file exists so the
 * fixture itself is verified, not just hand-copied constants.
 */
beforeEach(function (): void {
    $path = dirname(__DIR__, 2) . '/fixtures/fsrs-vectors.json';

    $this->vectors = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    $this->fsrs = new Fsrs(new Parameters($this->vectors['parameters']));
    $this->dr = $this->vectors['desired_retention'];
    $this->max = $this->vectors['maximum_interval'];
});

it('ships the same defaults the fixture pins', function (): void {
    expect($this->vectors['parameters'])->toBe(Parameters::DEFAULTS)
        ->and($this->vectors['constants']['decay'])->toBe(Parameters::DEFAULTS[20]);
});

it('derives the fixture FACTOR', function (): void {
    $expected = $this->vectors['constants']['factor'];
    $tolerance = $this->vectors['constants']['factor_tolerance'];

    expect(abs($this->fsrs->parameters()->factor - $expected))->toBeLessThan($tolerance);
});

it('matches the fixture forgetting curve', function (): void {
    foreach ($this->vectors['forgetting_curve'] as $case) {
        expect(round($this->fsrs->retrievability((float) $case['elapsed_days'], (float) $case['stability']), 4))
            ->toBe($case['retrievability'], "t={$case['elapsed_days']}");
    }
});

it('matches the fixture initial state', function (): void {
    foreach ($this->vectors['initial_state'] as $case) {
        $grade = Rating::from($case['grade']);

        expect(round($this->fsrs->initialStability($grade), 4))->toBe($case['stability'])
            ->and(round($this->fsrs->initialDifficulty($grade), 4))->toBe($case['difficulty'])
            ->and($this->fsrs->intervalDays($this->fsrs->initialStability($grade), $this->dr, $this->max))
            ->toBe($case['interval']);
    }
});

it('matches the fixture repeated-Good progression', function (): void {
    $s = null;
    $d = null;
    $day = 0;
    $interval = 0;

    foreach ($this->vectors['repeated_good'] as $row) {
        $elapsed = $interval;
        $day += $elapsed;

        expect($day)->toBe($row['day'], "day at review {$row['review']}");

        if ($s === null) {
            $r = 1.0;
            $s = $this->fsrs->initialStability(Rating::Good);
            $d = $this->fsrs->initialDifficulty(Rating::Good);
        } else {
            $r = $this->fsrs->retrievability((float) $elapsed, $s);
            $d = $this->fsrs->nextDifficulty($d, Rating::Good);
            $s = $this->fsrs->stabilityAfterRecall($d, $s, $r, Rating::Good);
        }

        expect(round($r, 4))->toBe($row['retrievability_before'], "R before {$row['review']}")
            ->and(round($s, 4))->toBe($row['stability_after'], "S after {$row['review']}")
            ->and(round($d, 4))->toBe($row['difficulty_after'], "D after {$row['review']}");

        $interval = $this->fsrs->intervalDays($s, $this->dr, $this->max);
        expect($interval)->toBe($row['next_interval'], "interval after {$row['review']}");
    }
});

it('matches the fixture single review from a fixed state', function (): void {
    $fixture = $this->vectors['single_review'];
    $s = (float) $fixture['stability_before'];
    $d = (float) $fixture['difficulty_before'];

    $r = $this->fsrs->retrievability((float) $fixture['elapsed_days'], $s);
    expect(round($r, 10))->toBe($fixture['retrievability_before']);

    foreach ($fixture['cases'] as $case) {
        $grade = Rating::from($case['grade']);
        $newD = $this->fsrs->nextDifficulty($d, $grade);
        $newS = $grade->isLapse()
            ? $this->fsrs->stabilityAfterLapse($newD, $s, $r)
            : $this->fsrs->stabilityAfterRecall($newD, $s, $r, $grade);

        expect(round($newD, 4))->toBe($case['difficulty_after'], "D grade {$case['grade']}")
            ->and(round($newS, 4))->toBe($case['stability_after'], "S grade {$case['grade']}")
            ->and($this->fsrs->intervalDays($newS, $this->dr, $this->max))->toBe($case['interval']);
    }
});

it('matches the fixture same-day stability', function (): void {
    $before = (float) $this->vectors['same_day']['stability_before'];

    foreach ($this->vectors['same_day']['cases'] as $case) {
        expect(round($this->fsrs->stabilitySameDay($before, Rating::from($case['grade'])), 6))
            ->toBe($case['stability_after'], "grade {$case['grade']}");
    }
});

it('matches the fixture interval multipliers', function (): void {
    foreach ($this->vectors['interval_multipliers'] as $case) {
        expect($this->fsrs->intervalDays((float) $case['stability'], (float) $case['desired_retention'], $this->max))
            ->toBe($case['interval'], "DR={$case['desired_retention']}");
    }
});

it('matches the fixture for corrupted memory state', function (): void {
    foreach ($this->vectors['corrupted_memory_state']['cases'] as $case) {
        $scheduler = new App\Fsrs\Scheduler(
            new App\Fsrs\SchedulerConfig(rolloverHour: 4, timezone: 'UTC', enableFuzzing: false),
            $this->fsrs,
        );

        $card = new App\Fsrs\CardSnapshot(
            state: App\Fsrs\CardState::Review,
            stability: $case['stability'],
            difficulty: $case['difficulty'],
            lastReview: new DateTimeImmutable('2026-01-01T10:00:00Z'),
            reps: 5,
        );

        $out = $scheduler->review($card, Rating::Good, new DateTimeImmutable('2026-01-11T10:00:00Z'));

        expect($out->stability)->not->toBeNan()
            ->and(round($out->stability, 4))->toBe(round($case['expected_stability'], 4), $case['description'])
            ->and(round($out->difficulty, 4))->toBe(round($case['expected_difficulty'], 4), $case['description']);
    }
});

it('matches the fixture first-rating transitions for a new card', function (): void {
    $scheduler = new App\Fsrs\Scheduler(
        new App\Fsrs\SchedulerConfig(rolloverHour: 4, timezone: 'UTC', enableFuzzing: false),
        $this->fsrs,
    );

    foreach ($this->vectors['new_card_transitions']['cases'] as $case) {
        $out = $scheduler->review(App\Fsrs\CardSnapshot::new(), Rating::from($case['rating']), new DateTimeImmutable('2026-01-01T10:00:00Z'));

        expect($out->state->value)->toBe($case['state'], "rating {$case['rating']}")
            ->and($out->step)->toBe($case['step'], "rating {$case['rating']}")
            ->and($out->scheduledSeconds)->toBe($case['seconds'], "rating {$case['rating']}");
    }
});

it('matches the fixture day-difference cases across timezones and DST', function (): void {
    foreach ($this->vectors['day_difference']['cases'] as $case) {
        $scheduler = new App\Fsrs\Scheduler(
            new App\Fsrs\SchedulerConfig(rolloverHour: 4, timezone: $case['timezone']),
            $this->fsrs,
        );

        $from = new DateTimeImmutable($case['from']);
        $to = new DateTimeImmutable($case['to']);

        expect($scheduler->dayDifference($from, $to))->toBe($case['days'], $case['description']);
    }
});
