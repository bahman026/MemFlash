<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\StaleReviewException;
use App\Fsrs\CardState;
use App\Fsrs\Rating;
use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Deck;
use App\Models\ReviewLog;
use App\Models\StaticCard;
use App\Models\StaticDeck;
use App\Models\User;
use App\Models\UserStaticCardState;
use App\Models\UserStaticDeckProgress;
use App\Services\ReviewService;
use Carbon\CarbonInterface;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Offline support.
 *
 * The client downloads everything it needs once, studies with no network using the
 * JavaScript mirror of the scheduler, and queues each rating locally. On
 * reconnect it posts the queue here.
 *
 * THE SERVER IS AUTHORITATIVE. Offline ratings are replayed through the PHP
 * scheduler with their original timestamps, and the response carries the
 * resulting state so the client can overwrite whatever it computed locally. The
 * mirror exists to keep the session usable, not to be the source of truth.
 */
class SyncController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviews,
    ) {}

    /**
     * Everything needed to study offline: decks, cards, memory state and presets.
     *
     * Deliberately one request. A client about to lose connectivity cannot
     * paginate, so this returns the full working set.
     *
     * `?deck=card:12` or `?deck=static_card:5` returns just that deck, in the
     * same shape. The study screen refreshes the deck it is about to study that
     * way instead of downloading everything again; 404 when the deck is not the
     * caller's to study.
     */
    public function bootstrap(Request $request): JsonResponse
    {
        $user = $request->user();

        $only = null;
        if ($request->filled('deck')) {
            abort_unless(preg_match('/^(card|static_card):(\d+)$/', (string) $request->query('deck'), $m) === 1, 422, 'deck must look like card:12 or static_card:5.');
            $only = [$m[1], (int) $m[2]];
        }

        $decks = $only === null || $only[0] === 'card' ? $this->personalDecks($user, $only[1] ?? null) : [];
        $staticDecks = $only === null || $only[0] === 'static_card' ? $this->staticDecks($user, $only[1] ?? null) : [];

        abort_if($only !== null && $decks === [] && $staticDecks === [], 404);

        return response()->json([
            'synced_at' => now()->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'timezone' => $user->timezone,
                'rollover_hour' => (int) $user->rollover_hour,
                'level' => $user->level->value,
                // The instant today's study day began. The client counts new cards
                // it starts offline against the daily limit until the next one.
                'study_day_started_at' => $this->reviews->startOfStudyDay($user)->toIso8601String(),
            ],
            'decks' => $decks,
            'static_decks' => $staticDecks,
        ]);
    }

    /**
     * Replay a queue of offline ratings.
     *
     * Each entry carries a client-generated uuid. Replaying the same uuid is a
     * no-op that returns the original result, so an interrupted sync can be
     * retried safely -- without that, a dropped response would double-schedule the
     * card and permanently corrupt the optimizer's training data, because
     * review_logs is append-only.
     *
     * One bad entry does not fail the batch: it is reported in `rejected` so the
     * client can drop it instead of retrying forever.
     */
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reviews' => 'required|array|max:1000',
            'reviews.*.client_uuid' => 'required|uuid',
            'reviews.*.type' => 'required|in:card,static_card',
            'reviews.*.card_id' => 'required|integer',
            'reviews.*.rating' => 'required|integer|min:1|max:4',
            'reviews.*.reviewed_at' => 'required|date',
            'reviews.*.review_duration_ms' => 'nullable|integer|min:0',
        ]);

        $user = $request->user();
        $applied = [];
        $rejected = [];

        // Oldest first: replaying out of order would compute each interval from the
        // wrong elapsed time.
        $queue = collect($validated['reviews'])
            ->sortBy(fn (array $r): string => $r['reviewed_at'])
            ->values();

        // First reviews of lesson cards per lesson, for the lesson progress bars.
        // The online study endpoints record these as they go; replayed offline
        // reviews have to be counted here or offline study never moves progress.
        $firstStaticReviews = [];

        foreach ($queue as $entry) {
            try {
                $alreadyApplied = ReviewLog::where('client_uuid', $entry['client_uuid'])->exists();
                $result = $this->replay($user, $entry);
                $applied[] = $result;

                if ($entry['type'] === 'static_card' && ! $alreadyApplied && $result['first_review']) {
                    $deckId = (int) StaticCard::whereKey($entry['card_id'])->value('static_deck_id');
                    $firstStaticReviews[$deckId] = ($firstStaticReviews[$deckId] ?? 0) + 1;
                }
            } catch (StaleReviewException $e) {
                $rejected[] = [
                    'client_uuid' => $entry['client_uuid'],
                    'reason' => $e->getMessage(),
                ];
            } catch (\Throwable $e) {
                Log::warning('Offline review rejected during sync', [
                    'user_id' => $user->id,
                    'client_uuid' => $entry['client_uuid'],
                    'error' => $e->getMessage(),
                ]);

                $rejected[] = [
                    'client_uuid' => $entry['client_uuid'],
                    'reason' => 'Could not be applied.',
                ];
            }
        }

        foreach ($firstStaticReviews as $deckId => $count) {
            UserStaticDeckProgress::recordStudied($user, $deckId, $count);
        }

        return response()->json([
            'synced_at' => now()->toIso8601String(),
            'applied' => $applied,
            'rejected' => $rejected,
        ]);
    }

    /**
     * Refuse a queued review older than the card's current state.
     *
     * Rated offline on a phone on Monday, reviewed online on a laptop on
     * Wednesday, synced on Thursday: replaying Monday's rating would roll the
     * card's last review and due date back to Monday and throw away Wednesday's
     * timing. The later review already decided the schedule, so the stale one is
     * reported and dropped. A retry of a review already applied is not stale; it
     * is recognised by its client_uuid further down.
     */
    private function rejectIfSuperseded(string $uuid, DateTimeImmutable $reviewedAt, ?CarbonInterface $lastReview): void
    {
        if ($lastReview === null || $reviewedAt >= $lastReview->toDateTimeImmutable()) {
            return;
        }

        if (ReviewLog::where('client_uuid', $uuid)->exists()) {
            return;
        }

        throw new StaleReviewException('Superseded by a later review of this card.');
    }

    /**
     * Apply one queued rating and return the authoritative state.
     *
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    private function replay(User $user, array $entry): array
    {
        // A device clock running ahead would otherwise stamp the card with a last
        // review in the future, and every later review would see negative elapsed
        // time. The server's clock wins.
        $reviewedAt = min(new DateTimeImmutable((string) $entry['reviewed_at']), new DateTimeImmutable);
        $rating = Rating::from((int) $entry['rating']);
        $uuid = (string) $entry['client_uuid'];
        $duration = $entry['review_duration_ms'] ?? null;

        if ($entry['type'] === 'card') {
            $card = Card::with('deck')->findOrFail($entry['card_id']);

            // Ownership is checked per entry: a queue is client-supplied data.
            abort_unless($card->deck->user_id === $user->id, 403);

            $this->rejectIfSuperseded($uuid, $reviewedAt, $card->last_review);

            $outcome = $this->reviews->reviewCard(
                user: $user,
                card: $card,
                rating: $rating,
                durationMs: $duration,
                now: $reviewedAt,
                clientUuid: $uuid,
                offline: true,
            );
        } else {
            $card = StaticCard::with('staticDeck')->findOrFail($entry['card_id']);

            $this->rejectIfSuperseded($uuid, $reviewedAt, $card->stateFor($user)->last_review);

            $outcome = $this->reviews->reviewStaticCard(
                user: $user,
                card: $card,
                rating: $rating,
                durationMs: $duration,
                now: $reviewedAt,
                clientUuid: $uuid,
                offline: true,
            );
        }

        return [
            'client_uuid' => $uuid,
            'type' => $entry['type'],
            'card_id' => (int) $entry['card_id'],
            'state' => $outcome->state->value,
            'step' => $outcome->step,
            'stability' => $outcome->stability,
            'difficulty' => $outcome->difficulty,
            'due' => $outcome->due->format(DATE_ATOM),
            'last_review' => $outcome->lastReview->format(DATE_ATOM),
            'reps' => $outcome->reps,
            'lapses' => $outcome->lapses,
            'first_review' => $outcome->stateBefore === CardState::New,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function personalDecks(User $user, ?int $onlyId = null): array
    {
        return $user->decks()
            ->when($onlyId !== null, fn ($query) => $query->whereKey($onlyId))
            ->with(['cards', 'config'])
            ->get()
            ->map(fn (Deck $deck): array => [
                'id' => $deck->id,
                'name' => $deck->name,
                'new_cards_per_day' => $deck->new_cards_per_day,
                'new_cards_today' => $this->reviews->newCardsStartedToday($user, Card::class, $deck->cards->pluck('id')),
                'config' => $this->configPayload($deck->configOrDefault()),
                'cards' => $deck->cards->map(fn (Card $card): array => [
                    'id' => $card->id,
                    'front' => $card->front,
                    'back' => $card->back,
                    'description' => $card->description,
                    'audio' => $card->audio,
                    'state' => $card->state->value,
                    'step' => $card->step,
                    'stability' => $card->stability,
                    'difficulty' => $card->difficulty,
                    'due' => $card->due?->format(DATE_ATOM),
                    'last_review' => $card->last_review?->format(DATE_ATOM),
                    'reps' => $card->reps,
                    'lapses' => $card->lapses,
                    'suspended' => $card->suspended,
                ])->all(),
            ])->all();
    }

    /**
     * Static decks for the user's level, with their per-user memory state.
     *
     * @return array<int, array<string, mixed>>
     */
    private function staticDecks(User $user, ?int $onlyId = null): array
    {
        // The full download covers the user's level; a single lesson can be any,
        // since the study screens let a user open lessons outside their level.
        $decks = $onlyId === null
            ? $user->getRecommendedStaticDecks()->load('cards')
            : StaticDeck::whereKey($onlyId)->with('cards')->get();

        $states = UserStaticCardState::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('static_card_id');

        $settings = $user->staticDeckSettings()->get()->keyBy('static_deck_id');

        return $decks->map(function ($deck) use ($states, $settings, $user): array {
            /** @var \App\Models\UserStaticDeckSetting|null $setting */
            $setting = $settings->get($deck->id);

            return [
                'id' => $deck->id,
                'name' => $deck->name,
                'level' => $deck->level->value,
                'lesson_number' => $deck->lesson_number,
                'cards_per_day' => $setting !== null ? $setting->cards_per_day : 10,
                'new_cards_today' => $this->reviews->newCardsStartedToday($user, StaticCard::class, $deck->cards->pluck('id')),
                'config' => $setting !== null
                    ? $this->configPayload($setting)
                    : $this->configPayload($user->staticDeckSettings()->make()),
                'cards' => $deck->cards->map(fn (StaticCard $card): array => [
                    'id' => $card->id,
                    'front' => $card->front,
                    'back' => $card->back,
                    'audio' => $card->audio,
                    ...$this->memoryPayload($states->get($card->id)),
                ])->all(),
            ];
        })->all();
    }

    /**
     * Memory state for one shared card.
     *
     * Takes a nullable state on purpose: a card the user has never seen has no row,
     * and it is reported as New rather than being created here -- a GET must not
     * write.
     *
     * @return array<string, mixed>
     */
    private function memoryPayload(?UserStaticCardState $state): array
    {
        if ($state === null) {
            return [
                'state' => 'new',
                'step' => null,
                'stability' => null,
                'difficulty' => null,
                'due' => null,
                'last_review' => null,
                'reps' => 0,
                'lapses' => 0,
                'suspended' => false,
            ];
        }

        return [
            'state' => $state->state->value,
            'step' => $state->step,
            'stability' => $state->stability,
            'difficulty' => $state->difficulty,
            'due' => $state->due?->format(DATE_ATOM),
            'last_review' => $state->last_review?->format(DATE_ATOM),
            'reps' => $state->reps,
            'lapses' => $state->lapses,
            'suspended' => (bool) $state->suspended,
        ];
    }

    /**
     * The preset in the shape the JavaScript scheduler expects.
     *
     * @return array<string, mixed>
     */
    private function configPayload(object $config): array
    {
        return [
            'parameters' => $config->parameters ?: \App\Fsrs\Parameters::DEFAULTS,
            'desiredRetention' => (float) ($config->desired_retention ?: 0.9),
            'learningSteps' => $config->learning_steps ?: [60, 600],
            'relearningSteps' => $config->relearning_steps ?: [600],
            'maximumInterval' => (int) ($config->maximum_interval ?: 36500),
            'enableFuzzing' => (bool) ($config->enable_fuzzing ?? true),
        ];
    }

    /**
     * How many reviews this user has logged, so the client can show progress
     * toward the optimizer threshold.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'synced_at' => now()->toIso8601String(),
            'logged_reviews' => ReviewLog::where('user_id', $user->id)->count(),
        ]);
    }
}
