<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Fsrs\Rating;
use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Deck;
use App\Models\ReviewLog;
use App\Models\StaticCard;
use App\Models\User;
use App\Models\UserStaticCardState;
use App\Services\ReviewService;
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
     */
    public function bootstrap(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'synced_at' => now()->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'timezone' => $user->timezone,
                'rollover_hour' => (int) $user->rollover_hour,
                'level' => $user->level->value,
            ],
            'decks' => $this->personalDecks($user),
            'static_decks' => $this->staticDecks($user),
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

        foreach ($queue as $entry) {
            try {
                $applied[] = $this->replay($user, $entry);
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

        return response()->json([
            'synced_at' => now()->toIso8601String(),
            'applied' => $applied,
            'rejected' => $rejected,
        ]);
    }

    /**
     * Apply one queued rating and return the authoritative state.
     *
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    private function replay(User $user, array $entry): array
    {
        $reviewedAt = new DateTimeImmutable((string) $entry['reviewed_at']);
        $rating = Rating::from((int) $entry['rating']);
        $uuid = (string) $entry['client_uuid'];
        $duration = $entry['review_duration_ms'] ?? null;

        if ($entry['type'] === 'card') {
            $card = Card::with('deck')->findOrFail($entry['card_id']);

            // Ownership is checked per entry: a queue is client-supplied data.
            abort_unless($card->deck->user_id === $user->id, 403);

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
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function personalDecks(User $user): array
    {
        return $user->decks()
            ->with(['cards', 'config'])
            ->get()
            ->map(fn (Deck $deck): array => [
                'id' => $deck->id,
                'name' => $deck->name,
                'new_cards_per_day' => $deck->new_cards_per_day,
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
    private function staticDecks(User $user): array
    {
        $decks = $user->getRecommendedStaticDecks()->load('cards');

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
