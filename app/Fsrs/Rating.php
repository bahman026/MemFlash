<?php

declare(strict_types=1);

namespace App\Fsrs;

/**
 * The four review grades.
 *
 * Hard is a PASSING grade. Only Again is a lapse and routes to the post-lapse
 * stability formula (F7). UI copy must not imply that Hard is a failure.
 */
enum Rating: int
{
    case Again = 1;
    case Hard = 2;
    case Good = 3;
    case Easy = 4;

    public function label(): string
    {
        return match ($this) {
            self::Again => 'Again',
            self::Hard => 'Hard',
            self::Good => 'Good',
            self::Easy => 'Easy',
        };
    }

    /**
     * Anything but Again counts as a successful recall.
     */
    public function isLapse(): bool
    {
        return $this === self::Again;
    }
}
