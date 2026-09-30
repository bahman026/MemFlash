<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Fsrs\Optimizer\Optimizer;
use App\Fsrs\Optimizer\TrainingSet;
use App\Models\Deck;
use App\Models\StaticDeck;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Fits a deck's FSRS parameters to its own review history.
 *
 * Queued because it is CPU-bound: every iteration replays every logged review
 * twice per parameter, so a deck with thousands of reviews takes far longer than a
 * request should. Running it inline would tie up a php-fpm worker.
 *
 * Unique per deck: two concurrent runs would fit the same history twice and race
 * on the same config row.
 */
class OptimizeFsrsParameters implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Numerical gradients over a long history are slow, so give it room. A deck
     * with 5,000 reviews can legitimately take several minutes.
     */
    public int $timeout = 900;

    /**
     * Do not retry automatically: a failure here is either not enough data, which
     * a retry cannot fix, or a bug worth seeing in the log.
     */
    public int $tries = 1;

    public function __construct(
        public readonly int $userId,
        public readonly string $deckType,
        public readonly int $deckId,
    ) {}

    public function uniqueId(): string
    {
        return "{$this->userId}:{$this->deckType}:{$this->deckId}";
    }

    public function handle(): void
    {
        $user = User::find($this->userId);

        if ($user === null) {
            return;
        }

        $set = $this->deckType === 'static'
            ? TrainingSet::forStaticDeck($this->userId, $this->deckId)
            : TrainingSet::forDeck($this->userId, $this->deckId);

        $optimizer = new Optimizer($set);

        if (! $optimizer->hasEnoughData()) {
            Log::info('FSRS optimization skipped: not enough history', [
                'user_id' => $this->userId,
                'deck' => $this->uniqueId(),
                'usable_reviews' => $set->predictableCount(),
                'required' => Optimizer::MINIMUM_REVIEWS,
            ]);

            return;
        }

        $result = $optimizer->run();

        // A fit that does not beat the defaults comes back AS the defaults. Saving
        // it anyway overwrote a deck's previous, better fit with them every week,
        // and stamped optimized_at as if something had been learned. The --sync
        // path in the command already skipped the write.
        if ($result['improved']) {
            $this->persist($user, $result);
        }

        Log::info('FSRS optimization complete', [
            'deck' => $this->uniqueId(),
            'reviews' => $result['reviews'],
            'log_loss' => round($result['initial_loss'], 5) . ' -> ' . round($result['final_loss'], 5),
            'rmse' => round($result['initial_rmse'], 5) . ' -> ' . round($result['final_rmse'], 5),
            'improved' => $result['improved'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function persist(User $user, array $result): void
    {
        $payload = [
            'parameters' => $result['parameters'],
            'optimized_at' => now(),
            'optimized_review_count' => $result['reviews'],
        ];

        if ($this->deckType === 'static') {
            $deck = StaticDeck::find($this->deckId);

            if ($deck === null) {
                return;
            }

            // The defaults apply only when the setting row is new; updateOrCreate
            // with them reset the learner's own cards_per_day on every run.
            $user->staticDeckSettings()
                ->firstOrCreate(['static_deck_id' => $deck->id], ['cards_per_day' => 10, 'is_active' => true])
                ->update($payload);

            return;
        }

        $deck = Deck::where('user_id', $user->id)->find($this->deckId);

        if ($deck === null) {
            throw new RuntimeException("Deck {$this->deckId} does not belong to user {$user->id}.");
        }

        $deck->configOrDefault()->update($payload);
    }
}
