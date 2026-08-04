<?php

declare(strict_types=1);

namespace App\Fsrs\Optimizer;

use App\Fsrs\Fsrs;
use App\Fsrs\Parameters;
use App\Fsrs\Rating;

/**
 * Scores a candidate parameter set against real review history.
 *
 * Every review is a binary outcome -- success (rating >= 2) or lapse (rating = 1)
 * -- and the model predicts a probability R beforehand, so this is binary
 * classification and the objective is log loss.
 *
 * Each card's history is replayed forward through F1 to F8 to reconstruct the S
 * and D the model would have held at that moment. R is never read from the log:
 * that is the whole point, since the stored R came from the old parameters.
 */
class Evaluator
{
    /**
     * Keeps ln(R) finite when the model is very confident and wrong.
     */
    private const EPSILON = 1e-10;

    public function __construct(
        private readonly TrainingSet $set,
    ) {}

    /**
     * Mean binary cross-entropy over every predictable review.
     *
     * Lower is better. Returns INF for an empty set so a caller comparing losses
     * can never accept it as an improvement.
     */
    public function logLoss(Parameters $parameters): float
    {
        $fsrs = new Fsrs($parameters);
        $total = 0.0;
        $count = 0;

        foreach ($this->set->sequences as $sequence) {
            foreach ($this->replay($fsrs, $sequence) as [$predicted, $success]) {
                $r = min(1 - self::EPSILON, max(self::EPSILON, $predicted));
                $total += $success ? -log($r) : -log(1 - $r);
                $count++;
            }
        }

        return $count === 0 ? INF : $total / $count;
    }

    /**
     * Root mean squared error between predicted and measured retention, bucketed.
     *
     * Log loss says how well calibrated the model is per review; this says whether
     * "90%" actually means 90% in aggregate, which is what a user can interpret.
     */
    public function rmse(Parameters $parameters, int $buckets = 20): float
    {
        $fsrs = new Fsrs($parameters);
        $sums = [];
        $hits = [];
        $counts = [];

        foreach ($this->set->sequences as $sequence) {
            foreach ($this->replay($fsrs, $sequence) as [$predicted, $success]) {
                $bucket = min($buckets - 1, (int) floor($predicted * $buckets));
                $sums[$bucket] = ($sums[$bucket] ?? 0.0) + $predicted;
                $hits[$bucket] = ($hits[$bucket] ?? 0) + ($success ? 1 : 0);
                $counts[$bucket] = ($counts[$bucket] ?? 0) + 1;
            }
        }

        $total = array_sum($counts);
        if ($total === 0) {
            return INF;
        }

        $weighted = 0.0;
        foreach ($counts as $bucket => $n) {
            $meanPredicted = $sums[$bucket] / $n;
            $measured = $hits[$bucket] / $n;
            $weighted += $n * ($meanPredicted - $measured) ** 2;
        }

        return sqrt($weighted / $total);
    }

    /**
     * True retention measured from the log, ignoring the model entirely.
     */
    public function measuredRetention(): float
    {
        $successes = 0;
        $total = 0;

        foreach ($this->set->sequences as $sequence) {
            foreach (array_slice($sequence, 1) as $review) {
                $successes += $review['rating'] >= Rating::Hard->value ? 1 : 0;
                $total++;
            }
        }

        return $total === 0 ? 0.0 : $successes / $total;
    }

    /**
     * Replay one card, yielding [predicted R, was a success] per predictable review.
     *
     * @param  list<array{rating: int, elapsed_days: int}>  $sequence
     * @return \Generator<int, array{0: float, 1: bool}>
     */
    private function replay(Fsrs $fsrs, array $sequence): \Generator
    {
        $stability = null;
        $difficulty = null;

        foreach ($sequence as $index => $review) {
            $rating = Rating::from($review['rating']);
            $elapsed = (float) $review['elapsed_days'];

            if ($index === 0) {
                // First review: R is 1 by definition, so it predicts nothing.
                $stability = $fsrs->initialStability($rating);
                $difficulty = $fsrs->initialDifficulty($rating);

                continue;
            }

            $predicted = $fsrs->retrievability($elapsed, $stability);

            yield [$predicted, ! $rating->isLapse()];

            // Difficulty before stability: both stability formulas take the new D.
            $difficulty = $fsrs->nextDifficulty($difficulty, $rating);

            if ($elapsed < 1) {
                $stability = $fsrs->stabilitySameDay($stability, $rating);
            } elseif ($rating->isLapse()) {
                $stability = $fsrs->stabilityAfterLapse($difficulty, $stability, $predicted);
            } else {
                $stability = $fsrs->stabilityAfterRecall($difficulty, $stability, $predicted, $rating);
            }
        }
    }
}
