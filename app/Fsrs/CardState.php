<?php

declare(strict_types=1);

namespace App\Fsrs;

enum CardState: string
{
    case New = 'new';
    case Learning = 'learning';
    case Review = 'review';
    case Relearning = 'relearning';

    /**
     * States that walk through sub-day steps.
     */
    public function isStepped(): bool
    {
        return $this === self::Learning || $this === self::Relearning;
    }
}
