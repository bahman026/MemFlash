<?php

declare(strict_types=1);

use App\Fsrs\Fsrs;
use App\Fsrs\Parameters;
use App\Fsrs\Rating;

/**
 * Reference vectors from the FSRS-6 build specification, Part 4.
 * Default parameters, desired_retention = 0.90, fuzzing disabled.
 */
beforeEach(function (): void {
    $this->fsrs = new Fsrs(new Parameters);
    $this->dr = 0.90;
    $this->maxInterval = 36500;
});

// -------------------------------------------------------------------------
// 4.1 Invariants
// -------------------------------------------------------------------------

it('derives FACTOR from w[20]', function (): void {
    expect($this->fsrs->parameters()->factor)->toBeGreaterThan(0.9803464944 - 1e-9)
        ->and($this->fsrs->parameters()->factor)->toBeLessThan(0.9803464944 + 1e-9);
});

it('satisfies R(S, S) == 0.9 for every S', function (float $s): void {
    expect($this->fsrs->retrievability($s, $s))->toBeGreaterThan(0.9 - 1e-12)
        ->and($this->fsrs->retrievability($s, $s))->toBeLessThan(0.9 + 1e-12);
})->with([0.001, 0.5, 1.0, 2.3065, 10.0, 100.0, 3278.5315, 36500.0]);

it('satisfies I(0.9, S) == S for every S', function (float $s): void {
    expect($this->fsrs->intervalDays($s, 0.90, $this->maxInterval))->toBe((int) round($s));
})->with([1.0, 2.0, 10.0, 46.2632, 100.0, 496.6417, 1342.4723]);

it('recomputes FACTOR when w[20] changes', function (): void {
    $w = Parameters::DEFAULTS;
    $w[20] = 0.5;
    $p = new Parameters($w);

    expect($p->factor)->toEqual(0.9 ** (-1.0 / 0.5) - 1.0)
        ->and((new Fsrs($p))->retrievability(7.0, 7.0))->toBeGreaterThan(0.9 - 1e-12);
});

it('clamps out-of-range parameters', function (): void {
    $w = Parameters::DEFAULTS;
    $w[15] = 5.0;   // hard penalty must land inside (0, 1)
    $w[16] = 99.0;  // easy bonus must land inside (1, 6)
    $w[20] = 2.0;   // decay must land inside [0.1, 0.8]
    $p = new Parameters($w);

    expect($p->get(15))->toBeLessThan(1.0)
        ->and($p->get(16))->toBe(6.0)
        ->and($p->get(20))->toBe(0.8);
});

// -------------------------------------------------------------------------
// 4.2 Initial state by first grade
// -------------------------------------------------------------------------

it('computes initial state for each first grade', function (Rating $g, float $s0, float $d0, int $interval): void {
    expect(round($this->fsrs->initialStability($g), 4))->toBe($s0)
        ->and(round($this->fsrs->initialDifficulty($g), 4))->toBe($d0)
        ->and($this->fsrs->intervalDays($this->fsrs->initialStability($g), $this->dr, $this->maxInterval))->toBe($interval);
})->with([
    'Again' => [Rating::Again, 0.2120, 6.4133, 1],
    'Hard' => [Rating::Hard, 1.2931, 5.1122, 1],
    'Good' => [Rating::Good, 2.3065, 2.1181, 2],
    'Easy' => [Rating::Easy, 8.2956, 1.0000, 8],
]);

// -------------------------------------------------------------------------
// 4.3 Repeated Good from a new card, reviewed exactly when due
// -------------------------------------------------------------------------

it('reproduces the repeated-Good progression', function (): void {
    $expected = [
        // [day, R before, S after, D after, next interval]
        [0,    1.0000,    2.3065,    2.1181,  2],
        [2,    0.9095,   10.9654,    2.1170, 11],
        [13,   0.8998,   46.2632,    2.1159, 46],
        [59,   0.9004,  162.7036,    2.1148, 163],
        [222,  0.8999,  496.6417,    2.1136, 497],
        [719,  0.9000, 1342.4723,    2.1125, 1342],
        [2061, 0.9000, 3278.5315,    2.1114, 3279],
    ];

    $s = null;
    $d = null;
    $day = 0;
    $interval = 0;

    foreach ($expected as $i => [$expDay, $expR, $expS, $expD, $expInterval]) {
        $elapsed = $interval;
        $day += $elapsed;

        expect($day)->toBe($expDay, 'day at review ' . ($i + 1));

        if ($s === null) {
            $r = 1.0;
            $s = $this->fsrs->initialStability(Rating::Good);
            $d = $this->fsrs->initialDifficulty(Rating::Good);
        } else {
            $r = $this->fsrs->retrievability((float) $elapsed, $s);
            $d = $this->fsrs->nextDifficulty($d, Rating::Good);
            $s = $this->fsrs->stabilityAfterRecall($d, $s, $r, Rating::Good);
        }

        expect(round($r, 4))->toBe($expR, 'R before review ' . ($i + 1))
            ->and(round($s, 4))->toBe($expS, 'S after review ' . ($i + 1))
            ->and(round($d, 4))->toBe($expD, 'D after review ' . ($i + 1));

        $interval = $this->fsrs->intervalDays($s, $this->dr, $this->maxInterval);
        expect($interval)->toBe($expInterval, 'interval after review ' . ($i + 1));
    }
});

// -------------------------------------------------------------------------
// 4.4 One review from a fixed state: S = 10, D = 5, elapsed = 10 (R = 0.9)
// -------------------------------------------------------------------------

it('reproduces a single review from a fixed state', function (Rating $g, float $expD, float $expS, int $expInterval): void {
    $s = 10.0;
    $d = 5.0;
    $elapsed = 10.0;

    $r = $this->fsrs->retrievability($elapsed, $s);
    expect(round($r, 10))->toBe(0.9);

    $newD = $this->fsrs->nextDifficulty($d, $g);
    $newS = $g->isLapse()
        ? $this->fsrs->stabilityAfterLapse($newD, $s, $r)
        : $this->fsrs->stabilityAfterRecall($newD, $s, $r, $g);

    expect(round($newD, 4))->toBe($expD)
        ->and(round($newS, 4))->toBe($expS)
        ->and($this->fsrs->intervalDays($newS, $this->dr, $this->maxInterval))->toBe($expInterval);
})->with([
    'Again' => [Rating::Again, 8.3475, 1.3489, 1],
    'Hard' => [Rating::Hard, 6.6718, 19.5559, 20],
    'Good' => [Rating::Good, 4.9960, 32.0414, 32],
    'Easy' => [Rating::Easy, 3.3202, 62.8033, 63],
]);

it('never lets a success reduce stability', function (Rating $g): void {
    $s = 10.0;
    $d = $this->fsrs->nextDifficulty(5.0, $g);

    expect($this->fsrs->stabilityAfterRecall($d, $s, 0.9, $g))->toBeGreaterThanOrEqual($s);
})->with([Rating::Hard, Rating::Good, Rating::Easy]);

it('never lets post-lapse stability exceed the previous stability', function (float $s): void {
    $d = $this->fsrs->nextDifficulty(5.0, Rating::Again);

    expect($this->fsrs->stabilityAfterLapse($d, $s, 0.9))->toBeLessThanOrEqual($s);
})->with([1.0, 10.0, 100.0, 1000.0]);

// -------------------------------------------------------------------------
// 4.5 Same-day review, S = 2.0
// -------------------------------------------------------------------------

it('reproduces same-day stability', function (Rating $g, float $expected): void {
    expect(round($this->fsrs->stabilitySameDay(2.0, $g), 6))->toBe($expected);
})->with([
    'Again' => [Rating::Again, 0.678422],
    'Hard' => [Rating::Hard, 1.167091],
    'Good' => [Rating::Good, 2.007749],
    'Easy' => [Rating::Easy, 3.453935],
]);

it('lets Hard and Again reduce S same-day but not Good or Easy', function (): void {
    $s = 2.0;

    expect($this->fsrs->stabilitySameDay($s, Rating::Again))->toBeLessThan($s)
        ->and($this->fsrs->stabilitySameDay($s, Rating::Hard))->toBeLessThan($s)
        ->and($this->fsrs->stabilitySameDay($s, Rating::Good))->toBeGreaterThanOrEqual($s)
        ->and($this->fsrs->stabilitySameDay($s, Rating::Easy))->toBeGreaterThanOrEqual($s);
});

// -------------------------------------------------------------------------
// 4.6 Forgetting curve samples, S = 10
// -------------------------------------------------------------------------

it('reproduces the forgetting curve', function (float $t, float $expected): void {
    expect(round($this->fsrs->retrievability($t, 10.0), 4))->toBe($expected);
})->with([
    [0.0, 1.0000],
    [1.0, 0.9857],
    [2.0, 0.9728],
    [5.0, 0.9403],
    [10.0, 0.9000],
    [20.0, 0.8459],
    [50.0, 0.7605],
    [100.0, 0.6928],
    [365.0, 0.5736],
]);

// -------------------------------------------------------------------------
// 1.5 Interval multipliers by desired retention
// -------------------------------------------------------------------------

it('produces the documented interval multipliers at S = 100', function (float $dr, int $expected): void {
    expect($this->fsrs->intervalDays(100.0, $dr, $this->maxInterval))->toBe($expected);
})->with([
    [0.70, 929],
    [0.80, 332],
    [0.85, 191],
    [0.90, 100],
    [0.95, 40],
    [0.97, 22],
]);

it('clamps difficulty into [1, 10] and floors stability', function (): void {
    $d = 5.0;
    for ($i = 0; $i < 200; $i++) {
        $d = $this->fsrs->nextDifficulty($d, Rating::Again);
    }
    expect($d)->toBeLessThanOrEqual(10.0)->toBeGreaterThanOrEqual(1.0);

    $d = 5.0;
    for ($i = 0; $i < 200; $i++) {
        $d = $this->fsrs->nextDifficulty($d, Rating::Easy);
    }
    expect($d)->toBeGreaterThanOrEqual(1.0);

    expect($this->fsrs->stabilityAfterLapse(10.0, Parameters::S_MIN, 0.01))
        ->toBeGreaterThanOrEqual(Parameters::S_MIN);
});
