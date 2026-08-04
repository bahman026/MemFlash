<?php

declare(strict_types=1);

namespace App\Fsrs\Fuzz;

/**
 * Deterministic source for tests. 0.0 yields the low end of the fuzz window,
 * 0.999... the high end.
 */
final class FixedFuzzSource implements FuzzSource
{
    public function __construct(private readonly float $value = 0.0) {}

    public function next(): float
    {
        return $this->value;
    }
}
