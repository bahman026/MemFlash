<?php

declare(strict_types=1);

namespace App\Fsrs\Fuzz;

final class RandomFuzzSource implements FuzzSource
{
    public function next(): float
    {
        return mt_rand() / (mt_getrandmax() + 1);
    }
}
