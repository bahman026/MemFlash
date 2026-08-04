/**
 * Parity tests for the offline FSRS mirror.
 *
 * Reads the same fixture as tests/Unit/Fsrs/FixtureParityTest.php. If the two
 * implementations ever diverge, one of the suites fails.
 *
 * Run: npm run test:js   (uses the Node test runner, no extra dependency)
 */

import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

import { Fsrs, Parameters, Scheduler, Rating, CardState, DEFAULT_PARAMETERS } from './fsrs.js';

const here = dirname(fileURLToPath(import.meta.url));
const vectors = JSON.parse(readFileSync(join(here, '../../../tests/fixtures/fsrs-vectors.json'), 'utf8'));

const fsrs = new Fsrs(new Parameters(vectors.parameters));
const DR = vectors.desired_retention;
const MAX = vectors.maximum_interval;

const round = (value, places) => Number(value.toFixed(places));

test('the fixture matches the shipped defaults', () => {
    assert.deepEqual(vectors.parameters, DEFAULT_PARAMETERS);
});

test('FACTOR is derived from w[20]', () => {
    const { factor, factor_tolerance: tolerance } = vectors.constants;
    assert.ok(Math.abs(fsrs.p.factor - factor) < tolerance, `got ${fsrs.p.factor}`);
});

test('R(S, S) is exactly 0.9 for every S', () => {
    for (const s of [0.001, 0.5, 1, 2.3065, 10, 100, 3278.5315, 36500]) {
        assert.ok(Math.abs(fsrs.retrievability(s, s) - 0.9) < 1e-12, `S=${s}`);
    }
});

test('I(0.9, S) returns S', () => {
    for (const s of [1, 2, 10, 46.2632, 100, 496.6417, 1342.4723]) {
        assert.equal(fsrs.intervalDays(s, 0.9, MAX), Math.round(s), `S=${s}`);
    }
});

test('out-of-range parameters are clamped', () => {
    const w = [...DEFAULT_PARAMETERS];
    w[15] = 5;
    w[16] = 99;
    w[20] = 2;
    const p = new Parameters(w);

    assert.ok(p.get(15) < 1);
    assert.equal(p.get(16), 6);
    assert.equal(p.get(20), 0.8);
});

test('forgetting curve matches the fixture', () => {
    for (const c of vectors.forgetting_curve) {
        assert.equal(
            round(fsrs.retrievability(c.elapsed_days, c.stability), 4),
            c.retrievability,
            `t=${c.elapsed_days}`
        );
    }
});

test('initial state matches the fixture for every first grade', () => {
    for (const c of vectors.initial_state) {
        assert.equal(round(fsrs.initialStability(c.grade), 4), c.stability, `S0 grade ${c.grade}`);
        assert.equal(round(fsrs.initialDifficulty(c.grade), 4), c.difficulty, `D0 grade ${c.grade}`);
        assert.equal(fsrs.intervalDays(fsrs.initialStability(c.grade), DR, MAX), c.interval, `I grade ${c.grade}`);
    }
});

test('the repeated-Good progression matches the fixture', () => {
    let s = null;
    let d = null;
    let day = 0;
    let interval = 0;

    for (const row of vectors.repeated_good) {
        const elapsed = interval;
        day += elapsed;
        assert.equal(day, row.day, `day at review ${row.review}`);

        let r;
        if (s === null) {
            r = 1.0;
            s = fsrs.initialStability(Rating.Good);
            d = fsrs.initialDifficulty(Rating.Good);
        } else {
            r = fsrs.retrievability(elapsed, s);
            d = fsrs.nextDifficulty(d, Rating.Good);
            s = fsrs.stabilityAfterRecall(d, s, r, Rating.Good);
        }

        assert.equal(round(r, 4), row.retrievability_before, `R before ${row.review}`);
        assert.equal(round(s, 4), row.stability_after, `S after ${row.review}`);
        assert.equal(round(d, 4), row.difficulty_after, `D after ${row.review}`);

        interval = fsrs.intervalDays(s, DR, MAX);
        assert.equal(interval, row.next_interval, `interval after ${row.review}`);
    }
});

test('a single review from a fixed state matches the fixture', () => {
    const { stability_before: s, difficulty_before: d, elapsed_days: elapsed } = vectors.single_review;

    const r = fsrs.retrievability(elapsed, s);
    assert.equal(round(r, 10), vectors.single_review.retrievability_before);

    for (const c of vectors.single_review.cases) {
        const newD = fsrs.nextDifficulty(d, c.grade);
        const newS =
            c.grade === Rating.Again
                ? fsrs.stabilityAfterLapse(newD, s, r)
                : fsrs.stabilityAfterRecall(newD, s, r, c.grade);

        assert.equal(round(newD, 4), c.difficulty_after, `D grade ${c.grade}`);
        assert.equal(round(newS, 4), c.stability_after, `S grade ${c.grade}`);
        assert.equal(fsrs.intervalDays(newS, DR, MAX), c.interval, `I grade ${c.grade}`);
    }
});

test('same-day stability matches the fixture', () => {
    for (const c of vectors.same_day.cases) {
        assert.equal(
            round(fsrs.stabilitySameDay(vectors.same_day.stability_before, c.grade), 6),
            c.stability_after,
            `grade ${c.grade}`
        );
    }
});

test('Hard and Again may reduce S same-day but Good and Easy may not', () => {
    const s = 2.0;
    assert.ok(fsrs.stabilitySameDay(s, Rating.Again) < s);
    assert.ok(fsrs.stabilitySameDay(s, Rating.Hard) < s);
    assert.ok(fsrs.stabilitySameDay(s, Rating.Good) >= s);
    assert.ok(fsrs.stabilitySameDay(s, Rating.Easy) >= s);
});

test('a success never reduces stability', () => {
    for (const grade of [Rating.Hard, Rating.Good, Rating.Easy]) {
        const d = fsrs.nextDifficulty(5, grade);
        assert.ok(fsrs.stabilityAfterRecall(d, 10, 0.9, grade) >= 10, `grade ${grade}`);
    }
});

test('post-lapse stability never exceeds the previous stability', () => {
    const d = fsrs.nextDifficulty(5, Rating.Again);
    for (const s of [1, 10, 100, 1000]) {
        assert.ok(fsrs.stabilityAfterLapse(d, s, 0.9) <= s, `S=${s}`);
    }
});

test('interval multipliers match the fixture', () => {
    for (const c of vectors.interval_multipliers) {
        assert.equal(fsrs.intervalDays(c.stability, c.desired_retention, MAX), c.interval, `DR=${c.desired_retention}`);
    }
});

// -------------------------------------------------------------------------
// Scheduler state machine
// -------------------------------------------------------------------------

const at = (iso) => new Date(iso);
const scheduler = (overrides = {}, random = () => 0) =>
    new Scheduler({ enableFuzzing: false, ...overrides }, random);

test('a new card enters learning on the first step', () => {
    const out = scheduler().review({ state: CardState.New }, Rating.Good, at('2026-01-01T10:00:00Z'));

    assert.equal(out.state, CardState.Learning);
    assert.equal(out.step, 0);
    assert.equal(out.scheduledSeconds, 60);
    assert.equal(out.elapsedDays, 0);
    assert.equal(out.retrievabilityBefore, 1);
    assert.equal(out.reps, 1);
});

test('a new card graduates when there are no learning steps', () => {
    const out = scheduler({ learningSteps: [] }).review(
        { state: CardState.New },
        Rating.Good,
        at('2026-01-01T10:00:00Z')
    );

    assert.equal(out.state, CardState.Review);
    assert.equal(out.scheduledDays, 2);
});

test('Hard on step 0 averages the first two steps', () => {
    const card = { state: CardState.Learning, step: 0, stability: 2.3065, difficulty: 2.1181, lastReview: at('2026-01-01T10:00:00Z') };
    const out = scheduler().review(card, Rating.Hard, at('2026-01-01T10:00:30Z'));

    assert.equal(out.scheduledSeconds, 330);
});

test('Hard on a single-step configuration multiplies by 1.5', () => {
    const card = { state: CardState.Learning, step: 0, stability: 2.3065, difficulty: 2.1181, lastReview: at('2026-01-01T10:00:00Z') };
    const out = scheduler({ learningSteps: [60] }).review(card, Rating.Hard, at('2026-01-01T10:00:30Z'));

    assert.equal(out.scheduledSeconds, 90);
});

test('a lapsed review card goes to relearning and matches the fixture', () => {
    const card = {
        state: CardState.Review,
        step: null,
        stability: 10,
        difficulty: 5,
        lastReview: at('2026-01-01T10:00:00Z'),
        reps: 5,
        lapses: 1,
    };
    const out = scheduler().review(card, Rating.Again, at('2026-01-11T10:00:00Z'));

    assert.equal(out.state, CardState.Relearning);
    assert.equal(out.step, 0);
    assert.equal(out.scheduledSeconds, 600);
    assert.equal(out.lapses, 2);
    assert.equal(out.elapsedDays, 10);
    assert.equal(round(out.stability, 4), 1.3489);
    assert.equal(round(out.difficulty, 4), 8.3475);
});

test('successful review intervals match the fixture end to end', () => {
    const card = {
        state: CardState.Review,
        step: null,
        stability: 10,
        difficulty: 5,
        lastReview: at('2026-01-01T10:00:00Z'),
        reps: 5,
    };

    for (const c of vectors.single_review.cases.filter((c) => c.grade !== Rating.Again)) {
        const out = scheduler().review(card, c.grade, at('2026-01-11T10:00:00Z'));
        assert.equal(out.state, CardState.Review);
        assert.equal(out.scheduledDays, c.interval, `grade ${c.grade}`);
    }
});

test('the rollover hour decides what counts as the same day', () => {
    const s = scheduler({ rolloverHour: 4 });

    // 23:00 to 02:00 crosses midnight but not the 4am rollover.
    assert.equal(s.dayDifference(at('2026-01-01T23:00:00Z'), at('2026-01-02T02:00:00Z')), 0);
    assert.equal(s.dayDifference(at('2026-01-01T23:00:00Z'), at('2026-01-02T05:00:00Z')), 1);
    // Only three hours apart, but they straddle 4am.
    assert.equal(s.dayDifference(at('2026-01-01T03:00:00Z'), at('2026-01-01T06:00:00Z')), 1);
});

test('a same-day review uses F8', () => {
    const card = { state: CardState.Review, stability: 2, difficulty: 5, lastReview: at('2026-01-01T10:00:00Z'), reps: 3 };
    const out = scheduler().review(card, Rating.Good, at('2026-01-01T22:00:00Z'));

    assert.equal(out.elapsedDays, 0);
    assert.equal(round(out.stability, 6), 2.007749);
});

test('fuzz stays inside the documented window', () => {
    const card = {
        state: CardState.Review,
        stability: 10,
        difficulty: 5,
        lastReview: at('2026-01-01T10:00:00Z'),
        reps: 5,
    };

    const low = scheduler({ enableFuzzing: true }, () => 0).review(card, Rating.Good, at('2026-01-11T10:00:00Z'));
    const high = scheduler({ enableFuzzing: true }, () => 0.9).review(card, Rating.Good, at('2026-01-11T10:00:00Z'));

    const window = vectors.fuzz_windows.find((w) => w.interval === 32);
    assert.equal(low.scheduledDays, window.min);
    assert.equal(high.scheduledDays, window.max);
});

test('intervals below 2.5 days are never fuzzed', () => {
    const out = scheduler({ learningSteps: [], enableFuzzing: true }, () => 0.999999).review(
        { state: CardState.New },
        Rating.Good,
        at('2026-01-01T10:00:00Z')
    );

    assert.equal(out.scheduledDays, 2);
});

test('handles corrupted memory state the same way PHP does', () => {
    for (const c of vectors.corrupted_memory_state.cases) {
        const card = {
            state: CardState.Review,
            stability: c.stability,
            difficulty: c.difficulty,
            lastReview: at('2026-01-01T10:00:00Z'),
            reps: 5,
        };

        const out = scheduler().review(card, Rating.Good, at('2026-01-11T10:00:00Z'));

        assert.ok(!Number.isNaN(out.stability), c.description);
        assert.equal(round(out.stability, 4), round(c.expected_stability, 4), c.description);
        assert.equal(round(out.difficulty, 4), round(c.expected_difficulty, 4), c.description);
    }
});

test('day-difference matches the fixture across timezones and DST', () => {
    for (const c of vectors.day_difference.cases) {
        const s = scheduler({ timezone: c.timezone });
        assert.equal(s.dayDifference(at(c.from), at(c.to)), c.days, c.description);
    }
});

test('preview returns all four ratings without mutating the card', () => {
    const card = {
        state: CardState.Review,
        stability: 10,
        difficulty: 5,
        lastReview: at('2026-01-01T10:00:00Z'),
        reps: 5,
    };
    const previews = scheduler().preview(card, at('2026-01-11T10:00:00Z'));

    assert.equal(previews[Rating.Hard].days, 20);
    assert.equal(previews[Rating.Good].days, 32);
    assert.equal(previews[Rating.Easy].days, 63);
    // Again goes to relearning in ten minutes, so it reports seconds not days.
    assert.equal(previews[Rating.Again].days, 0);
    assert.equal(previews[Rating.Again].seconds, 600);
    assert.equal(card.stability, 10);
    assert.equal(card.reps, 5);
});
