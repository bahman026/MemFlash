<?php

declare(strict_types=1);

namespace App\Fsrs;

use DateTimeImmutable;

/**
 * The scheduler's input: a card's memory state, detached from Eloquent.
 *
 * Retrievability is deliberately absent -- it is always derived.
 */
final class CardSnapshot
{
    public function __construct(
        public readonly CardState $state = CardState::New,
        public readonly ?int $step = null,
        public readonly ?float $stability = null,
        public readonly ?float $difficulty = null,
        public readonly ?DateTimeImmutable $lastReview = null,
        public readonly int $reps = 0,
        public readonly int $lapses = 0,
    ) {}

    public function isNew(): bool
    {
        return $this->state === CardState::New
            || $this->stability === null
            || $this->difficulty === null
            || $this->lastReview === null;
    }

    public static function new(): self
    {
        return new self;
    }
}
