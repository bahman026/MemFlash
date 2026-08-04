<?php

declare(strict_types=1);

namespace App\Fsrs\Optimizer;

use App\Models\ReviewLog;
use App\Models\StaticCard;
use Illuminate\Support\Collection;

/**
 * Review histories prepared for training.
 *
 * Each card becomes an ordered sequence of (rating, elapsed_days). The optimizer
 * replays those sequences through candidate parameters, so nothing here touches
 * the scheduler -- it only shapes the data.
 *
 * Per the specification: only the first review of a card on any given day counts.
 * Same-day repeats are excluded, because the short-term formula is a heuristic and
 * training on it would fit noise.
 */
class TrainingSet
{
    /**
     * @param  list<list<array{rating: int, elapsed_days: int}>>  $sequences
     */
    private function __construct(
        public readonly array $sequences,
        public readonly int $reviewCount,
        public readonly int $cardCount,
    ) {}

    /**
     * Build from the logs of one user's personal deck.
     */
    public static function forDeck(int $userId, int $deckId): self
    {
        $logs = ReviewLog::query()
            ->where('user_id', $userId)
            ->where('reviewable_type', \App\Models\Card::class)
            ->whereIn('reviewable_id', function ($query) use ($deckId): void {
                $query->select('id')->from('cards')->where('deck_id', $deckId);
            })
            ->orderBy('reviewable_id')
            ->orderBy('reviewed_at')
            ->get(['reviewable_id', 'rating', 'elapsed_days', 'reviewed_at']);

        return self::fromLogs($logs);
    }

    /**
     * Build from the logs of one user's shared curriculum deck.
     */
    public static function forStaticDeck(int $userId, int $staticDeckId): self
    {
        $logs = ReviewLog::query()
            ->where('user_id', $userId)
            ->where('reviewable_type', StaticCard::class)
            ->whereIn('reviewable_id', StaticCard::where('static_deck_id', $staticDeckId)->select('id'))
            ->orderBy('reviewable_id')
            ->orderBy('reviewed_at')
            ->get(['reviewable_id', 'rating', 'elapsed_days', 'reviewed_at']);

        return self::fromLogs($logs);
    }

    /**
     * @param  Collection<int, ReviewLog>  $logs
     */
    public static function fromLogs(Collection $logs): self
    {
        $sequences = [];
        $reviewCount = 0;

        foreach ($logs->groupBy('reviewable_id') as $cardLogs) {
            $seenDays = [];
            $sequence = [];

            foreach ($cardLogs->sortBy('reviewed_at') as $log) {
                // One review per card per day. The first wins.
                $day = $log->reviewed_at->toDateString();
                if (isset($seenDays[$day])) {
                    continue;
                }
                $seenDays[$day] = true;

                $sequence[] = [
                    'rating' => $log->rating->value,
                    'elapsed_days' => max(0, (int) $log->elapsed_days),
                ];
            }

            // A single review carries no signal: there is no prior state to predict
            // from, so it cannot contribute to the loss.
            if (count($sequence) < 2) {
                continue;
            }

            $sequences[] = $sequence;
            $reviewCount += count($sequence);
        }

        return new self($sequences, $reviewCount, count($sequences));
    }

    /**
     * Reviews that actually contribute to the loss: everything except each card's
     * first, which is predicted with R = 1 by definition.
     */
    public function predictableCount(): int
    {
        return $this->reviewCount - $this->cardCount;
    }

    public function isEmpty(): bool
    {
        return $this->sequences === [];
    }

    /**
     * First grades grouped for the initial stability estimate.
     *
     * @return array<int, list<array{elapsed_days: int, success: bool}>>
     */
    public function firstGradeGroups(): array
    {
        $groups = [1 => [], 2 => [], 3 => [], 4 => []];

        foreach ($this->sequences as $sequence) {
            $first = $sequence[0];
            $second = $sequence[1];

            $groups[$first['rating']][] = [
                'elapsed_days' => $second['elapsed_days'],
                'success' => $second['rating'] >= 2,
            ];
        }

        return $groups;
    }
}
