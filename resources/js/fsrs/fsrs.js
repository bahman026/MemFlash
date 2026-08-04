/**
 * FSRS-6 — offline mirror of app/Fsrs.
 *
 * This exists so a study session can schedule cards and label the answer buttons
 * with no network. The SERVER IS AUTHORITATIVE: every rating made offline is
 * queued raw and replayed through the PHP scheduler on sync, and whatever the
 * server returns overwrites the local state.
 *
 * Both implementations are tested against tests/fixtures/fsrs-vectors.json, so
 * this file cannot silently drift from the PHP one. If you change a formula here,
 * change it in app/Fsrs/Fsrs.php too and run both suites.
 *
 * Keep this file free of DOM and storage concerns — it is pure, like its
 * PHP counterpart, so it can be tested with no browser.
 */

export const DEFAULT_PARAMETERS = [
    0.212, 1.2931, 2.3065, 8.2956, 6.4133, 0.8334, 3.0194,
    0.001, 1.8722, 0.1666, 0.796, 1.4835, 0.0614, 0.2629,
    1.6483, 0.6014, 1.8729, 0.5425, 0.0912, 0.0658, 0.1542,
];

export const S_MIN = 0.001;
export const D_MIN = 1.0;
export const D_MAX = 10.0;
const MIN_EASE_FACTOR = 1.3;
const DEFAULT_EASE_FACTOR = 2.5;
const SECONDS_PER_DAY = 86400;

export const Rating = { Again: 1, Hard: 2, Good: 3, Easy: 4 };
export const CardState = { New: 'new', Learning: 'learning', Review: 'review', Relearning: 'relearning' };

/** Bounds enforced after optimization. Mirrors Parameters::CLAMPS. */
const CLAMPS = { 15: [0.001, 0.999], 16: [1.0, 6.0], 20: [0.1, 0.8] };

const clamp = (value, min, max) => Math.max(min, Math.min(max, value));

export class Parameters {
    constructor(w = null) {
        const weights = (w ?? DEFAULT_PARAMETERS).map(Number);

        if (weights.length !== 21) {
            throw new Error(`FSRS-6 requires exactly 21 parameters, got ${weights.length}.`);
        }

        for (const [index, [min, max]] of Object.entries(CLAMPS)) {
            weights[index] = clamp(weights[index], min, max);
        }

        this.w = weights;
        this.decay = weights[20];
        // Derived so that R(S, S) === 0.9 exactly. Never hard-code it: an
        // optimized w[20] changes this value.
        this.factor = Math.pow(0.9, -1 / this.decay) - 1;
    }

    get(i) {
        return this.w[i];
    }
}

export class Fsrs {
    constructor(parameters = new Parameters()) {
        this.p = parameters;
    }

    /** F1: R(t, S) = (1 + FACTOR * t / S) ^ (-DECAY) */
    retrievability(elapsedDays, stability) {
        if (stability <= 0) return 0;

        return Math.pow(1 + (this.p.factor * elapsedDays) / stability, -this.p.decay);
    }

    /** F2: I(DR, S) = (S / FACTOR) * (DR ^ (-1 / DECAY) - 1) */
    intervalDays(stability, desiredRetention, maximumInterval) {
        const days = (stability / this.p.factor) * (Math.pow(desiredRetention, -1 / this.p.decay) - 1);

        return Math.max(1, Math.min(maximumInterval, Math.round(days)));
    }

    /** F3 */
    initialStability(grade) {
        return Math.max(S_MIN, this.p.get(grade - 1));
    }

    /** F4 */
    initialDifficulty(grade) {
        return clamp(this.p.get(4) - Math.exp(this.p.get(5) * (grade - 1)) + 1, D_MIN, D_MAX);
    }

    /** F5: damping toward 10, then mean reversion toward D0(Easy). */
    nextDifficulty(difficulty, grade) {
        const deltaD = -this.p.get(6) * (grade - 3);
        const damped = difficulty + (deltaD * (10 - difficulty)) / 9;
        const target = this.initialDifficulty(Rating.Easy);

        return clamp(this.p.get(7) * target + (1 - this.p.get(7)) * damped, D_MIN, D_MAX);
    }

    /** F6: success after a day or more. S never decreases here, including Hard. */
    stabilityAfterRecall(difficulty, stability, retrievability, grade) {
        const hardPenalty = grade === Rating.Hard ? this.p.get(15) : 1;
        const easyBonus = grade === Rating.Easy ? this.p.get(16) : 1;

        const sInc =
            1 +
            Math.exp(this.p.get(8)) *
                (11 - difficulty) *
                Math.pow(stability, -this.p.get(9)) *
                (Math.exp(this.p.get(10) * (1 - retrievability)) - 1) *
                hardPenalty *
                easyBonus;

        return Math.max(S_MIN, stability * sInc);
    }

    /** F7: lapse. The min guarantees S never rises through a lapse. */
    stabilityAfterLapse(difficulty, stability, retrievability) {
        const longTerm =
            this.p.get(11) *
            Math.pow(difficulty, -this.p.get(12)) *
            (Math.pow(stability + 1, this.p.get(13)) - 1) *
            Math.exp(this.p.get(14) * (1 - retrievability));

        const shortTerm = stability / Math.exp(this.p.get(17) * this.p.get(18));

        return Math.max(S_MIN, Math.min(longTerm, shortTerm));
    }

    /**
     * F8: same-day review.
     *
     * Asymmetric with F6 on purpose: Hard and Again MAY reduce S here, Good and
     * Easy may not. Do not "tidy" this — it matches the reference implementation.
     */
    stabilitySameDay(stability, grade) {
        let sInc = Math.exp(this.p.get(17) * (grade - 3 + this.p.get(18))) * Math.pow(stability, -this.p.get(19));

        if (grade >= Rating.Good) {
            sInc = Math.max(1, sInc);
        }

        return Math.max(S_MIN, stability * sInc);
    }
}

/** Mirrors Scheduler::FUZZ_RANGES. */
const FUZZ_RANGES = [
    { start: 2.5, end: 7.0, factor: 0.15 },
    { start: 7.0, end: 20.0, factor: 0.1 },
    { start: 20.0, end: Infinity, factor: 0.05 },
];

export const defaultConfig = () => ({
    parameters: DEFAULT_PARAMETERS,
    desiredRetention: 0.9,
    learningSteps: [60, 600],
    relearningSteps: [600],
    maximumInterval: 36500,
    enableFuzzing: true,
    rolloverHour: 4,
});

export class Scheduler {
    /**
     * @param {object} config
     * @param {() => number} random injectable so tests can disable fuzz
     */
    constructor(config = {}, random = Math.random) {
        this.config = { ...defaultConfig(), ...config };
        this.fsrs = new Fsrs(new Parameters(this.config.parameters));
        this.random = random;
    }

    /**
     * Whole days between two instants, counted in rollover boundaries crossed.
     *
     * Mirrors Scheduler::dayDifference. A review at 2am belongs to the previous
     * day when rollover is 4am, and this decides whether F8 runs — so a naive
     * 24-hour difference would change scheduling.
     */
    dayDifference(from, to) {
        const shift = this.config.rolloverHour * 3600 * 1000;
        const startOfDay = (d) => {
            const shifted = new Date(d.getTime() - shift);
            return Date.UTC(shifted.getUTCFullYear(), shifted.getUTCMonth(), shifted.getUTCDate());
        };

        return Math.round((startOfDay(to) - startOfDay(from)) / (SECONDS_PER_DAY * 1000));
    }

    intervalDays(stability) {
        return this.fsrs.intervalDays(stability, this.config.desiredRetention, this.config.maximumInterval);
    }

    /**
     * Grade a card. Returns the new memory state plus the log payload.
     *
     * Order is enforced exactly as on the server: observe elapsed days and R
     * before mutating, then update D before S.
     *
     * @param {object} card {state, step, stability, difficulty, lastReview}
     * @param {number} rating 1..4
     * @param {Date} now
     */
    review(card, rating, now = new Date()) {
        const stateBefore = card.state ?? CardState.New;
        const isNew =
            stateBefore === CardState.New ||
            card.stability === null ||
            card.stability === undefined ||
            !card.lastReview;

        let elapsedDays = 0;
        let retrievability = 1.0;

        if (!isNew) {
            elapsedDays = this.dayDifference(new Date(card.lastReview), now);
            retrievability = this.fsrs.retrievability(elapsedDays, Number(card.stability));
        }

        let stability;
        let difficulty;

        if (isNew) {
            stability = this.fsrs.initialStability(rating);
            difficulty = this.fsrs.initialDifficulty(rating);
        } else {
            // Guards mirror the PHP: a null interval or zero ease factor would
            // otherwise collapse every future interval to zero.
            const currentStability = Number(card.stability) || DEFAULT_EASE_FACTOR;
            difficulty = this.fsrs.nextDifficulty(Number(card.difficulty) || DEFAULT_EASE_FACTOR, rating);

            if (elapsedDays < 1) {
                stability = this.fsrs.stabilitySameDay(currentStability, rating);
            } else if (rating === Rating.Again) {
                stability = this.fsrs.stabilityAfterLapse(difficulty, currentStability, retrievability);
            } else {
                stability = this.fsrs.stabilityAfterRecall(difficulty, currentStability, retrievability, rating);
            }
        }

        const reps = (card.reps ?? 0) + 1;
        const lapses = (card.lapses ?? 0) + (rating === Rating.Again ? 1 : 0);

        let [state, step, seconds] = this.transition(card, rating, stability, isNew);

        let scheduledDays = 0;
        if (state === CardState.Review) {
            let days = Math.round(seconds / SECONDS_PER_DAY);
            if (this.config.enableFuzzing) {
                days = this.applyFuzz(days, elapsedDays);
            }
            scheduledDays = days;
            seconds = days * SECONDS_PER_DAY;
        }

        return {
            state,
            step,
            stability,
            difficulty,
            due: new Date(now.getTime() + seconds * 1000),
            lastReview: now,
            reps,
            lapses,
            // log payload
            rating,
            stateBefore,
            elapsedDays,
            retrievabilityBefore: retrievability,
            scheduledDays,
            scheduledSeconds: seconds,
        };
    }

    /** What each of the four buttons would schedule. */
    preview(card, now = new Date()) {
        const out = {};
        for (const rating of [1, 2, 3, 4]) {
            const outcome = this.review(card, rating, now);
            out[rating] = {
                state: outcome.state,
                days: outcome.scheduledDays,
                seconds: outcome.scheduledSeconds,
            };
        }
        return out;
    }

    /** Mirrors Scheduler::transition. Returns [state, step, seconds]. */
    transition(card, rating, stability, isNew) {
        const graduate = () => [CardState.Review, null, this.intervalDays(stability) * SECONDS_PER_DAY];
        const { learningSteps, relearningSteps } = this.config;

        if (isNew) {
            if (learningSteps.length === 0) return graduate();
            return [CardState.Learning, 0, learningSteps[0]];
        }

        if (card.state === CardState.Review) {
            if (rating === Rating.Again && relearningSteps.length > 0) {
                return [CardState.Relearning, 0, relearningSteps[0]];
            }
            return graduate();
        }

        const steps = card.state === CardState.Relearning ? relearningSteps : learningSteps;
        const step = card.step ?? 0;

        if (steps.length === 0 || step >= steps.length) return graduate();

        switch (rating) {
            case Rating.Again:
                return [card.state, 0, steps[0]];
            case Rating.Hard:
                if (step === 0 && steps.length === 1) return [card.state, step, Math.round(steps[0] * 1.5)];
                if (step === 0) return [card.state, step, Math.round((steps[0] + steps[1]) / 2)];
                return [card.state, step, steps[step]];
            case Rating.Good:
                if (step + 1 >= steps.length) return graduate();
                return [card.state, step + 1, steps[step + 1]];
            case Rating.Easy:
                return graduate();
            default:
                return graduate();
        }
    }

    /** Mirrors Scheduler::applyFuzz. */
    applyFuzz(interval, elapsedDays) {
        if (interval < 2.5) return interval;

        let delta = 1.0;
        for (const range of FUZZ_RANGES) {
            delta += range.factor * Math.max(Math.min(interval, range.end) - range.start, 0);
        }

        let min = Math.max(2, Math.round(interval - delta));
        const max = Math.min(Math.round(interval + delta), this.config.maximumInterval);

        if (interval > elapsedDays) {
            min = Math.max(min, elapsedDays + 1);
        }
        min = Math.min(min, max);

        return Math.min(Math.round(this.random() * (max - min + 1) + min), this.config.maximumInterval);
    }
}
