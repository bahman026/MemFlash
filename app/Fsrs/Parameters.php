<?php

declare(strict_types=1);

namespace App\Fsrs;

use InvalidArgumentException;

/**
 * The 21 FSRS-6 weights, plus the constants derived from them.
 *
 * FACTOR is deliberately NOT a free parameter: it is derived so that
 * R(S, S) == 0.9 exactly, which is what makes the definition of stability
 * ("days until recall falls to 90%") self-consistent. It must be recomputed
 * whenever w[20] changes -- hard-coding it breaks every optimized parameter set.
 */
final class Parameters
{
    /**
     * FSRS-6 defaults, used until the optimizer has enough review history.
     *
     * @var list<float>
     */
    public const DEFAULTS = [
        0.212,  1.2931, 2.3065, 8.2956, 6.4133, 0.8334, 3.0194,
        0.001,  1.8722, 0.1666, 0.796,  1.4835, 0.0614, 0.2629,
        1.6483, 0.6014, 1.8729, 0.5425, 0.0912, 0.0658, 0.1542,
    ];

    public const COUNT = 21;

    public const S_MIN = 0.001;

    public const D_MIN = 1.0;

    public const D_MAX = 10.0;

    /**
     * Bounds on every weight, from fsrs-rs's parameter clipper (FSRS-6).
     *
     * Only w15, w16 and w20 used to be clamped, and the optimizer does ship
     * values outside the rest: on synthetic history it produced w7 < 0 in four of
     * nine runs, all marked as improvements, which makes difficulty drift away
     * from its mean instead of back toward it. w10 < 0 would let a success lower
     * stability, and w9 < 0 lets it grow without bound. The defaults sit inside
     * every range (w7 exactly on its floor).
     *
     * w15 stays strictly inside (0, 1), a little tighter than fsrs-rs's [0, 1].
     *
     * @var array<int, array{float, float}>
     */
    private const CLAMPS = [
        0 => [self::S_MIN, 100.0],   // initial stability, Again
        1 => [self::S_MIN, 100.0],   // initial stability, Hard
        2 => [self::S_MIN, 100.0],   // initial stability, Good
        3 => [self::S_MIN, 100.0],   // initial stability, Easy
        4 => [1.0, 10.0],            // initial difficulty
        5 => [0.001, 4.0],
        6 => [0.001, 4.0],           // difficulty change per grade
        7 => [0.001, 0.75],          // mean reversion
        8 => [0.0, 4.5],
        9 => [0.0, 0.8],
        10 => [0.001, 3.5],          // recall stability growth
        11 => [0.001, 5.0],
        12 => [0.001, 0.25],
        13 => [0.001, 0.9],
        14 => [0.0, 4.0],            // post-lapse stability
        15 => [0.001, 0.999],        // hard penalty, strictly inside (0, 1)
        16 => [1.0, 6.0],            // easy bonus
        17 => [0.0, 2.0],
        18 => [0.0, 2.0],            // same-day stability
        19 => [0.0, 0.8],
        20 => [0.1, 0.8],            // forgetting-curve decay
    ];

    /** @var list<float> */
    public readonly array $w;

    public readonly float $decay;

    public readonly float $factor;

    /**
     * @param  array<array-key, float|int|string>|null  $w
     */
    public function __construct(?array $w = null)
    {
        $w = $w === null
            ? self::DEFAULTS
            : array_values(array_map(static fn ($v): float => (float) $v, $w));

        if (count($w) !== self::COUNT) {
            throw new InvalidArgumentException(
                'FSRS-6 requires exactly ' . self::COUNT . ' parameters, got ' . count($w) . '.'
            );
        }

        foreach (self::CLAMPS as $index => [$min, $max]) {
            $w[$index] = max($min, min($max, $w[$index]));
        }

        $this->w = $w;
        $this->decay = $w[20];
        $this->factor = 0.9 ** (-1.0 / $this->decay) - 1.0;
    }

    public function get(int $index): float
    {
        return $this->w[$index];
    }

    /**
     * @return list<float>
     */
    public function toArray(): array
    {
        return $this->w;
    }
}
