<?php

declare(strict_types=1);

namespace App\Fsrs\Fuzz;

/**
 * Randomness for interval fuzzing, injected so tests can disable it.
 *
 * Every reference vector in the FSRS-6 spec assumes fuzz is off, so the
 * scheduler must never reach for a global random function directly.
 */
interface FuzzSource
{
    /**
     * A float in [0, 1).
     */
    public function next(): float;
}
