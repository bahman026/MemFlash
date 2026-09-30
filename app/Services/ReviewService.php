<?php

declare(strict_types=1);

namespace App\Services;

use App\Fsrs\CardSnapshot;
use App\Fsrs\CardState;
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
use Carbon\CarbonImmutable;
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
    /**
     * Anki's default ceiling on reviews per day. New cards have their own daily
     * limit; this only bounds how many due reviews one session loads.
     */
    public const MAX_REVIEWS_PER_SESSION = 200;

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
     * Current probability of recall, using the deck's own parameters and the
     * user's own rollover hour and timezone.
     *
     * HasFsrsMemory::retrievability() also computes this, but from the record
     * alone: it has no route to the deck's config, so it always uses the FSRS-6
     * defaults, and its day count is a plain Carbon diffInDays rather than a
     * rollover-aware one. Confirmed to diverge in both dimensions -- 0.809 vs
     * 0.777 for a deck with an optimized decay parameter at the same elapsed time
     * and stability, and a fractional "0.125 days" from Carbon where the correct,
     * rollover-aware count is 0. Prefer this method wherever a deck/user context
     * is available, which is every live call site.
     */
    public function retrievabilityOf(
        User $user,
        Card | StaticCard $card,
        ?DateTimeImmutable $now = null,
        ?CardSnapshot $snapshot = null,
    ): float {
        if ($card instanceof Card) {
            $card->loadMissing('deck');
            $scheduler = $this->schedulers->forDeck($card->deck, $user);
            $snapshot ??= $card->toSnapshot();
        } else {
            $card->loadMissing('staticDeck');
            $scheduler = $this->schedulers->forStaticDeck($card->staticDeck, $user);
            // Same reasoning as previewIntervals(): a GET must not write, so only
            // fall back to stateFor() (which creates a row) when the caller has not
            // already supplied one.
            $snapshot ??= $card->stateFor($user)->toSnapshot();
        }

        if ($snapshot->isNew()) {
            return 1.0;
        }

        $elapsedDays = $scheduler->dayDifference($snapshot->lastReview, $now ?? new DateTimeImmutable);

        return $scheduler->fsrs()->retrievability((float) $elapsedDays, (float) $snapshot->stability);
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
     * How many new cards this user may still start today among the given cards.
     *
     * The limit is per study day, not per page load: counted from the review log
     * (a card's first review is logged with state_before = new) since the day
     * began at the user's rollover hour in their timezone, as the scheduler
     * counts days. Before, each reload of the study queue handed out another
     * full batch, so the daily limit did not limit anything.
     *
     * @param  class-string<Model>  $type  Card or StaticCard
     * @param  mixed  $cardIds  a query selecting the ids of the cards in scope
     */
    public function newCardsLeftToday(User $user, int $dailyLimit, string $type, mixed $cardIds): int
    {
        $started = ReviewLog::query()
            ->where('user_id', $user->id)
            ->where('reviewable_type', (new $type)->getMorphClass())
            ->whereIn('reviewable_id', $cardIds)
            ->where('state_before', CardState::New->value)
            ->where('reviewed_at', '>=', $this->startOfStudyDay($user))
            ->count();

        return max(0, $dailyLimit - $started);
    }

    /**
     * When the user's current study day began, in UTC.
     */
    public function startOfStudyDay(User $user): CarbonImmutable
    {
        $local = CarbonImmutable::now($user->timezone ?: 'UTC');
        $start = $local->setTime((int) ($user->rollover_hour ?? 4), 0);

        return ($local->lt($start) ? $start->subDay() : $start)->utc();
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
