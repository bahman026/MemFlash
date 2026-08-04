<?php

declare(strict_types=1);

namespace App\Services;

use App\Fsrs\CardSnapshot;
use App\Fsrs\Rating;
use App\Fsrs\ReviewOutcome;
use App\Fsrs\Scheduler;
use App\Fsrs\SchedulerFactory;
use App\Models\Card;
use App\Models\Deck;
use App\Models\ReviewLog;
use App\Models\StaticCard;
use App\Models\StaticDeck;
use App\Models\User;
use App\Models\UserStaticCardState;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Records reviews: runs the scheduler, persists the new memory state, and writes
 * the mandatory review_logs row.
 *
 * The two card hierarchies differ only in where memory lives -- on the card row
 * for personal decks, on a per-user pivot for shared curriculum cards -- so both
 * paths converge on apply().
 */
class ReviewService
{
    public function __construct(
        private readonly SchedulerFactory $schedulers,
    ) {}

    /**
     * Grade a card in a personal deck.
     */
    public function reviewCard(
        User $user,
        Card $card,
        Rating $rating,
        ?int $durationMs = null,
        ?DateTimeImmutable $now = null,
        ?string $clientUuid = null,
        bool $offline = false,
    ): ReviewOutcome {
        $card->loadMissing('deck');

        return $this->apply(
            scheduler: $this->schedulers->forDeck($card->deck, $user),
            user: $user,
            memory: $card,
            reviewable: $card,
            rating: $rating,
            durationMs: $durationMs,
            now: $now,
            clientUuid: $clientUuid,
            offline: $offline,
        );
    }

    /**
     * Grade a shared curriculum card for one user.
     */
    public function reviewStaticCard(
        User $user,
        StaticCard $card,
        Rating $rating,
        ?int $durationMs = null,
        ?DateTimeImmutable $now = null,
        ?string $clientUuid = null,
        bool $offline = false,
    ): ReviewOutcome {
        $card->loadMissing('staticDeck');

        return $this->apply(
            scheduler: $this->schedulers->forStaticDeck($card->staticDeck, $user),
            user: $user,
            memory: $card->stateFor($user),
            reviewable: $card,
            rating: $rating,
            durationMs: $durationMs,
            now: $now,
            clientUuid: $clientUuid,
            offline: $offline,
        );
    }

    /**
     * What each rating would schedule, for the answer buttons.
     *
     * Returns days AND seconds rather than days alone: Again on a review card goes
     * to relearning in ten minutes, so a day count of 0 would be indistinguishable
     * from "unknown". The UI needs the seconds to render "10m" instead of "0d".
     *
     * @return array<int, array{state: string, days: int, seconds: int}>
     */
    public function previewIntervals(
        User $user,
        Card | StaticCard $card,
        ?DateTimeImmutable $now = null,
        ?CardSnapshot $snapshot = null,
    ): array {
        if ($card instanceof Card) {
            $card->loadMissing('deck');
            $scheduler = $this->schedulers->forDeck($card->deck, $user);
            $snapshot ??= $card->toSnapshot();
        } else {
            $card->loadMissing('staticDeck');
            $scheduler = $this->schedulers->forStaticDeck($card->staticDeck, $user);
            // Fall back to the saved state only when the caller has not supplied a
            // snapshot. Callers rendering a queue pass one in, so previewing never
            // writes a state row -- a GET must not create rows as a side effect.
            $snapshot ??= $card->stateFor($user)->toSnapshot();
        }

        $out = [];
        foreach ($scheduler->preview($snapshot, $now ?? new DateTimeImmutable) as $value => $outcome) {
            $out[$value] = [
                'state' => $outcome->state->value,
                'days' => $outcome->scheduledDays,
                'seconds' => $outcome->scheduledSeconds,
            ];
        }

        return $out;
    }

    /**
     * Return a card to the New state while keeping its review history.
     */
    public function forget(User $user, Card | StaticCard $card): void
    {
        $memory = $card instanceof Card ? $card : $card->stateFor($user);
        $memory->forget();
    }

    /**
     * Clear every card's memory in a deck. History in review_logs is untouched.
     */
    public function forgetDeck(User $user, Deck | StaticDeck $deck): int
    {
        if ($deck instanceof Deck) {
            return $deck->cards()->update(Card::forgottenState());
        }

        return UserStaticCardState::query()
            ->where('user_id', $user->id)
            ->whereIn('static_card_id', $deck->cards()->select('id'))
            ->update(UserStaticCardState::forgottenState());
    }

    /**
     * @param  Card|UserStaticCardState  $memory  the row holding FSRS state
     * @param  Model  $reviewable  what the log points at
     */
    private function apply(
        Scheduler $scheduler,
        User $user,
        Card | UserStaticCardState $memory,
        Model $reviewable,
        Rating $rating,
        ?int $durationMs,
        ?DateTimeImmutable $now,
        ?string $clientUuid,
        bool $offline,
    ): ReviewOutcome {
        $now ??= new DateTimeImmutable;

        return DB::transaction(function () use (
            $scheduler,
            $user,
            $memory,
            $reviewable,
            $rating,
            $durationMs,
            $now,
            $clientUuid,
            $offline
        ): ReviewOutcome {
            // An offline queue can be replayed after an interrupted sync. The unique
            // index on client_uuid is the real guard; this check avoids the exception
            // on the common path and lets the caller treat the retry as a success.
            if ($clientUuid !== null) {
                $existing = ReviewLog::where('client_uuid', $clientUuid)->first();

                if ($existing !== null) {
                    return $this->outcomeFromLog($existing, $memory);
                }
            }

            $outcome = $scheduler->review($memory->toSnapshot(), $rating, $now);

            $memory->applyOutcome($outcome)->save();

            ReviewLog::create([
                'user_id' => $user->id,
                'reviewable_type' => $reviewable->getMorphClass(),
                'reviewable_id' => $reviewable->getKey(),
                'rating' => $rating->value,
                'state_before' => $outcome->stateBefore->value,
                'elapsed_days' => $outcome->elapsedDays,
                'retrievability_before' => $outcome->retrievabilityBefore,
                'stability_after' => $outcome->stability,
                'difficulty_after' => $outcome->difficulty,
                'scheduled_days' => $outcome->scheduledDays,
                'review_duration_ms' => $durationMs,
                'reviewed_at' => $outcome->lastReview,
                'client_uuid' => $clientUuid,
                'synced_offline' => $offline,
            ]);

            return $outcome;
        });
    }

    /**
     * Rebuild an outcome from an already-recorded log, so a replayed offline
     * review returns the same answer instead of scheduling the card twice.
     */
    private function outcomeFromLog(ReviewLog $log, Card | UserStaticCardState $memory): ReviewOutcome
    {
        return new ReviewOutcome(
            state: $memory->state,
            step: $memory->step,
            stability: (float) $log->stability_after,
            difficulty: (float) $log->difficulty_after,
            due: $memory->due?->toDateTimeImmutable() ?? $log->reviewed_at->toDateTimeImmutable(),
            lastReview: $log->reviewed_at->toDateTimeImmutable(),
            reps: $memory->reps,
            lapses: $memory->lapses,
            rating: $log->rating,
            stateBefore: $log->state_before,
            elapsedDays: $log->elapsed_days,
            retrievabilityBefore: (float) $log->retrievability_before,
            scheduledDays: $log->scheduled_days,
            scheduledSeconds: $log->scheduled_days * 86400,
        );
    }
}
