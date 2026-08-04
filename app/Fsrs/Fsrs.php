<?php

declare(strict_types=1);

namespace App\Fsrs;

/**
 * FSRS-6 memory model -- formulas F1 through F8.
 *
 * The DSR model describes each card with three quantities:
 *
 *   S = Stability       days for recall probability to fall from 100% to 90%
 *   D = Difficulty      how hard this item is for this user, in [1, 10]
 *   R = Retrievability  probability of recall right now, in [0, 1]
 *
 * R is NEVER persisted -- it is always recomputed from S and elapsed days.
 * Storing R is the single most common implementation bug.
 *
 * This class is pure: no clock, no database, no framework. Every method is a
 * function of its arguments, which is what makes it testable against the
 * published reference vectors.
 *
 * @see https://github.com/open-spaced-repetition/awesome-fsrs/wiki/The-Algorithm
 */
final class Fsrs
{
    public function __construct(
        private readonly Parameters $p = new Parameters,
    ) {}

    public function parameters(): Parameters
    {
        return $this->p;
    }

    // -----------------------------------------------------------------
    // F1 / F2 -- forgetting curve and its inverse
    // -----------------------------------------------------------------

    /**
     * F1: R(t, S) = (1 + FACTOR * t / S) ^ (-DECAY)
     *
     * A power function, not an exponential. A superposition of many individual
     * exponential forgetting curves is better approximated by a power law, and
     * real review data is exactly such a mixture. The practical consequence is a
     * heavy tail: R falls steeply before t = S then flattens, so a card overdue
     * by months is not as lost as an exponential model would predict.
     */
    public function retrievability(float $elapsedDays, float $stability): float
    {
        if ($stability <= 0.0) {
            return 0.0;
        }

        return (1.0 + $this->p->factor * $elapsedDays / $stability) ** (-$this->p->decay);
    }

    /**
     * F2: I(DR, S) = (S / FACTOR) * (DR ^ (-1 / DECAY) - 1)
     *
     * The inverse of F1, solved for t. At DR = 0.9 this returns exactly S --
     * that identity is the invariant proving the implementation is correct.
     */
    public function intervalDays(float $stability, float $desiredRetention, int $maximumInterval): int
    {
        $days = ($stability / $this->p->factor)
            * ($desiredRetention ** (-1.0 / $this->p->decay) - 1.0);

        return (int) max(1, min($maximumInterval, (int) round($days)));
    }

    // -----------------------------------------------------------------
    // F3 / F4 -- initial state on the very first review
    // -----------------------------------------------------------------

    /**
     * F3: S0(G) = w[G - 1]
     */
    public function initialStability(Rating $g): float
    {
        return max(Parameters::S_MIN, $this->p->get($g->value - 1));
    }

    /**
     * F4: D0(G) = clamp(w[4] - e^(w[5] * (G - 1)) + 1, 1, 10)
     */
    public function initialDifficulty(Rating $g): float
    {
        return $this->clampDifficulty(
            $this->p->get(4) - exp($this->p->get(5) * ($g->value - 1)) + 1.0
        );
    }

    // -----------------------------------------------------------------
    // F5 -- difficulty update
    // -----------------------------------------------------------------

    /**
     * F5, in three ordered steps:
     *
     *   delta_D  = -w[6] * (G - 3)
     *   D_damped = D + delta_D * (10 - D) / 9      linear damping toward 10
     *   D_new    = w[7] * D0(Easy) + (1 - w[7]) * D_damped
     *
     * Damping means D approaches 10 asymptotically but never reaches it. Mean
     * reversion pulls D back toward D0(Easy) over time, which is what prevents
     * the runaway drift SM-2 users call "ease hell".
     *
     * The reversion target is D0(4), not w[4] directly.
     *
     * Known limitation: D does not depend on R. Recalling a card when R was 1%
     * is far more informative than recalling it at R = 90%, and this formula
     * ignores that. The algorithm's authors are aware; no better formulation has
     * been found yet. Do not "fix" it here -- it would break compatibility.
     */
    public function nextDifficulty(float $difficulty, Rating $g): float
    {
        $deltaD = -$this->p->get(6) * ($g->value - 3);
        $damped = $difficulty + $deltaD * (10.0 - $difficulty) / 9.0;
        $target = $this->initialDifficulty(Rating::Easy);

        return $this->clampDifficulty(
            $this->p->get(7) * $target + (1.0 - $this->p->get(7)) * $damped
        );
    }

    // -----------------------------------------------------------------
    // F6 / F7 / F8 -- stability updates
    // -----------------------------------------------------------------

    /**
     * F6: stability after a successful review (Hard, Good, Easy), elapsed >= 1 day.
     *
     *   S_inc = 1 + e^w[8] * (11 - D) * S^(-w[9]) * (e^(w[10] * (1 - R)) - 1)
     *             * hard_penalty * easy_bonus
     *
     * Four properties follow directly, and they are the whole intuition of FSRS:
     *   higher D  -> smaller gain (hard material stabilises slowly)
     *   higher S  -> smaller gain (stability saturates)
     *   lower  R  -> larger gain  (the spacing effect)
     *   S_inc >= 1 always, so a success never reduces S -- including Hard.
     *
     * $difficulty must already be the post-F5 value.
     */
    public function stabilityAfterRecall(float $difficulty, float $stability, float $retrievability, Rating $g): float
    {
        $hardPenalty = $g === Rating::Hard ? $this->p->get(15) : 1.0;
        $easyBonus = $g === Rating::Easy ? $this->p->get(16) : 1.0;

        $sInc = 1.0 + exp($this->p->get(8))
            * (11.0 - $difficulty)
            * $stability ** (-$this->p->get(9))
            * (exp($this->p->get(10) * (1.0 - $retrievability)) - 1.0)
            * $hardPenalty
            * $easyBonus;

        return max(Parameters::S_MIN, $stability * $sInc);
    }

    /**
     * F7: stability after a lapse (Again), elapsed >= 1 day.
     *
     *   S_long  = w[11] * D^(-w[12]) * ((S + 1)^w[13] - 1) * e^(w[14] * (1 - R))
     *   S_short = S / e^(w[17] * w[18])
     *   S_new   = max(min(S_long, S_short), S_MIN)
     *
     * The min guarantees post-lapse stability can never exceed the stability
     * before the lapse. Note f(D) is non-linear here, unlike the linear (11 - D)
     * in F6 -- that is an empirical result, not a design choice.
     *
     * $difficulty must already be the post-F5 value.
     */
    public function stabilityAfterLapse(float $difficulty, float $stability, float $retrievability): float
    {
        $longTerm = $this->p->get(11)
            * $difficulty ** (-$this->p->get(12))
            * (($stability + 1.0) ** $this->p->get(13) - 1.0)
            * exp($this->p->get(14) * (1.0 - $retrievability));

        $shortTerm = $stability / exp($this->p->get(17) * $this->p->get(18));

        return max(Parameters::S_MIN, min($longTerm, $shortTerm));
    }

    /**
     * F8: stability for a same-day review (elapsed < 1 day).
     *
     *   S_inc = e^(w[17] * (G - 3 + w[18])) * S^(-w[19])
     *   if G >= 3: S_inc = max(S_inc, 1)
     *
     * The asymmetry with F6 is intentional: on a same-day review Hard and Again
     * MAY reduce S, while Good and Easy may not. In F6, Hard cannot reduce S.
     * Do not tidy this up -- it matches the reference implementation.
     *
     * This is a crude heuristic; neither FSRS-5 nor FSRS-6 models short-term
     * memory properly.
     */
    public function stabilitySameDay(float $stability, Rating $g): float
    {
        $sInc = exp($this->p->get(17) * ($g->value - 3 + $this->p->get(18)))
            * $stability ** (-$this->p->get(19));

        if ($g->value >= Rating::Good->value) {
            $sInc = max(1.0, $sInc);
        }

        return max(Parameters::S_MIN, $stability * $sInc);
    }

    private function clampDifficulty(float $d): float
    {
        return max(Parameters::D_MIN, min(Parameters::D_MAX, $d));
    }
}
