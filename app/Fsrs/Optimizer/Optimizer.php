<?php

declare(strict_types=1);

namespace App\Fsrs\Optimizer;

use App\Fsrs\Parameters;
use RuntimeException;

/**
 * Fits the 21 FSRS parameters to one deck's review history.
 *
 * Two stages, as the specification lays out:
 *
 *   1. Estimate w[0..3] -- the initial stability per first grade -- from the
 *      outcome of each card's second review, by asking which stability best
 *      explains the measured retention at that interval.
 *   2. Gradient descent on all 21 against log loss.
 *
 * Gradients are numerical (central differences) rather than analytic. That costs
 * two loss evaluations per parameter per step, but the derivation is where a
 * hand-written optimizer usually goes quietly wrong, and being slower inside a
 * queued job is a much better trade than being subtly incorrect. Correctness is
 * checked instead: the loss must fall, and the result must beat the defaults or it
 * is discarded.
 */
class Optimizer
{
    /**
     * Below this the result overfits, so optimizing is refused rather than
     * producing confident nonsense.
     */
    public const MINIMUM_REVIEWS = 400;

    /**
     * Where the fit is reliable enough to stop warning about sample size.
     */
    public const RECOMMENDED_REVIEWS = 1000;

    private const MAX_ITERATIONS = 60;

    private const INITIAL_LEARNING_RATE = 0.05;

    /** Stop when an iteration improves the loss by less than this. */
    private const CONVERGENCE = 1e-6;

    private const FINITE_DIFFERENCE = 1e-4;

    public function __construct(
        private readonly TrainingSet $set,
    ) {}

    public function hasEnoughData(): bool
    {
        return $this->set->predictableCount() >= self::MINIMUM_REVIEWS;
    }

    /**
     * @return array{
     *     parameters: list<float>,
     *     initial_loss: float,
     *     final_loss: float,
     *     initial_rmse: float,
     *     final_rmse: float,
     *     measured_retention: float,
     *     reviews: int,
     *     cards: int,
     *     iterations: int,
     *     improved: bool,
     * }
     */
    public function run(?callable $onProgress = null): array
    {
        if (! $this->hasEnoughData()) {
            throw new RuntimeException(sprintf(
                'Not enough review history to optimize: %d usable reviews, %d required.',
                $this->set->predictableCount(),
                self::MINIMUM_REVIEWS,
            ));
        }

        $evaluator = new Evaluator($this->set);

        $baseline = new Parameters;
        $initialLoss = $evaluator->logLoss($baseline);
        $initialRmse = $evaluator->rmse($baseline);

        // Stage 1: seed the initial-stability weights from measured retention.
        $weights = $this->seedInitialStability(Parameters::DEFAULTS);

        $best = new Parameters($weights);
        $bestLoss = $evaluator->logLoss($best);
        $learningRate = self::INITIAL_LEARNING_RATE;
        $iterations = 0;

        // Stage 2: gradient descent on all 21.
        for ($step = 0; $step < self::MAX_ITERATIONS; $step++) {
            $iterations++;
            $gradient = $this->gradient($evaluator, $best->toArray(), $bestLoss);

            $candidateWeights = $best->toArray();
            foreach ($gradient as $i => $slope) {
                $candidateWeights[$i] -= $learningRate * $slope;
            }

            // Parameters::__construct clamps w[15], w[16] and w[20] and recomputes
            // FACTOR, so an out-of-range step cannot escape.
            $candidate = new Parameters($candidateWeights);
            $candidateLoss = $evaluator->logLoss($candidate);

            if ($onProgress !== null) {
                $onProgress($step + 1, $candidateLoss, $bestLoss);
            }

            if ($candidateLoss < $bestLoss - self::CONVERGENCE) {
                $improvement = $bestLoss - $candidateLoss;
                $best = $candidate;
                $bestLoss = $candidateLoss;

                if ($improvement < self::CONVERGENCE) {
                    break;
                }

                continue;
            }

            // Overshot: halve the step and try again, give up once it is tiny.
            $learningRate /= 2;
            if ($learningRate < 1e-5) {
                break;
            }
        }

        // Never ship a fit that is worse than the defaults.
        $improved = $bestLoss < $initialLoss;

        return [
            'parameters' => $improved ? $best->toArray() : Parameters::DEFAULTS,
            'initial_loss' => $initialLoss,
            'final_loss' => $improved ? $bestLoss : $initialLoss,
            'initial_rmse' => $initialRmse,
            'final_rmse' => $improved ? $evaluator->rmse($best) : $initialRmse,
            'measured_retention' => $evaluator->measuredRetention(),
            'reviews' => $this->set->predictableCount(),
            'cards' => $this->set->cardCount,
            'iterations' => $iterations,
            'improved' => $improved,
        ];
    }

    /**
     * Central-difference gradient of the loss with respect to each weight.
     *
     * @param  list<float>  $weights
     * @return list<float>
     */
    private function gradient(Evaluator $evaluator, array $weights, float $currentLoss): array
    {
        $gradient = [];
        $h = self::FINITE_DIFFERENCE;

        for ($i = 0; $i < Parameters::COUNT; $i++) {
            $up = $weights;
            $down = $weights;
            $up[$i] += $h;
            $down[$i] -= $h;

            $lossUp = $evaluator->logLoss(new Parameters($up));
            $lossDown = $evaluator->logLoss(new Parameters($down));

            // A clamped weight yields identical losses either side, so its gradient
            // is naturally zero and the parameter simply stops moving.
            $gradient[$i] = ($lossUp - $lossDown) / (2 * $h);

            if (! is_finite($gradient[$i])) {
                $gradient[$i] = 0.0;
            }
        }

        return $gradient;
    }

    /**
     * Stage 1: estimate w[0..3] from the second review of each card.
     *
     * For each first grade, take the measured retention of the following review and
     * ask which stability makes F1 predict it at that interval. Inverting
     * R = (1 + FACTOR * t / S) ^ -DECAY gives S directly, so no curve fitting is
     * needed for the single-interval case.
     *
     * Groups with too little data keep the default weight.
     *
     * @param  list<float>  $weights
     * @return list<float>
     */
    private function seedInitialStability(array $weights): array
    {
        $parameters = new Parameters($weights);
        $minimumPerGroup = 20;

        foreach ($this->set->firstGradeGroups() as $grade => $observations) {
            if (count($observations) < $minimumPerGroup) {
                continue;
            }

            $intervals = array_map(static fn (array $o): int => max(1, $o['elapsed_days']), $observations);
            $successes = array_filter($observations, static fn (array $o): bool => $o['success']);

            $retention = count($successes) / count($observations);
            $meanInterval = array_sum($intervals) / count($intervals);

            // Guard the degenerate ends: 0% or 100% retention gives no finite answer.
            $retention = min(0.99, max(0.01, $retention));

            $stability = ($parameters->factor * $meanInterval)
                / (pow($retention, -1 / $parameters->decay) - 1);

            $weights[$grade - 1] = max(Parameters::S_MIN, $stability);
        }

        return $weights;
    }
}
