<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Fsrs\Rating;
use App\Models\StaticCard;
use App\Models\StaticDeck;
use App\Models\User;
use App\Models\UserStaticCardState;
use App\Models\UserStaticDeckProgress;
use App\Models\UserStaticDeckSetting;
use App\Services\ReviewService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Static decks are shared curriculum content, but scheduling is per learner:
 * memory state lives on user_static_card_states, so one user studying a lesson no
 * longer rewrites the schedule everybody else sees.
 */
class StaticDeckController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviews,
    ) {}

    /**
     * Reset this user's progress through a static deck.
     */
    public function reset(Request $request, StaticDeck $staticDeck): RedirectResponse
    {
        $user = $request->user();

        // Only this user's rows. The old version updated the shared static_cards
        // table, which rewound every other learner's schedule too.
        $staticDeck->resetLearningProgressFor($user);

        UserStaticDeckProgress::where('user_id', $user->id)
            ->where('static_deck_id', $staticDeck->id)
            ->update([
                'cards_studied' => 0,
                'last_studied_at' => null,
                'completed_at' => null,
            ]);

        return redirect()->back()->with('success', "Learning progress for {$staticDeck->name} has been reset successfully!");
    }

    /**
     * Show static deck details
     */
    public function show(StaticDeck $staticDeck)
    {
        $staticDeck->load('cards');

        return view('static-decks.show', compact('staticDeck'));
    }

    /**
     * Start studying a static deck
     */
    public function study(Request $request, StaticDeck $staticDeck)
    {
        $user = $request->user();
        $cardsPerDay = $this->cardsPerDayFor($user, $staticDeck);

        $dueCards = $this->dueCardsFor($user, $staticDeck, $cardsPerDay);

        if ($dueCards->isEmpty()) {
            return redirect()->back()->with('info', 'All cards in this deck are up to date! Great job!');
        }

        return view('static-decks.study', compact('staticDeck', 'dueCards', 'cardsPerDay'));
    }

    /**
     * Preview static deck cards
     */
    public function preview(StaticDeck $staticDeck)
    {
        $staticDeck->load('cards');

        return view('static-decks.preview', compact('staticDeck'));
    }

    /**
     * Update cards per day setting for a static deck
     */
    public function updateCardsPerDay(Request $request, StaticDeck $staticDeck): RedirectResponse
    {
        $request->validate([
            'cards_per_day' => 'required|integer|min:1|max:100',
        ]);

        $user = $request->user();

        UserStaticDeckSetting::updateOrCreate(
            ['user_id' => $user->id, 'static_deck_id' => $staticDeck->id],
            ['cards_per_day' => (int) $request->cards_per_day, 'is_active' => true],
        );

        return redirect()->back()->with('success', "Cards per day updated to {$request->cards_per_day} for {$staticDeck->name}!");
    }

    /**
     * Get cards for static deck study session
     */
    public function getCards(Request $request, StaticDeck $staticDeck): JsonResponse
    {
        try {
            $user = $request->user();

            // Same daily cap as study(). Without the limit this endpoint handed back
            // every due card, so the cards-per-day setting was enforced on the
            // rendered page and silently bypassed through the JSON path.
            $cardsPerDay = $this->cardsPerDayFor($user, $staticDeck);
            $dueCards = $this->dueCardsFor($user, $staticDeck, $cardsPerDay);

            // Load existing state in one query. Cards with no row have never been
            // seen; they get an unsaved instance so this GET stays read-only and
            // does not create a row per card just to render the queue.
            $states = UserStaticCardState::query()
                ->where('user_id', $user->id)
                ->whereIn('static_card_id', $dueCards->pluck('id'))
                ->get()
                ->keyBy('static_card_id');

            $cards = $dueCards->map(function (StaticCard $card) use ($user, $states): array {
                $state = $states->get($card->id)
                    ?? new UserStaticCardState(['user_id' => $user->id, 'static_card_id' => $card->id]);

                return [
                    'id' => $card->id,
                    'front' => $card->front,
                    'back' => $card->back,
                    'audio' => $card->audio,
                    'pronunciation' => $card->pronunciation(),
                    'state' => $state->state->value,
                    'stability' => $state->stability,
                    'difficulty' => $state->difficulty,
                    'retrievability' => $state->retrievability(),
                    'due' => $state->due,
                    'last_review' => $state->last_review,
                    'reps' => $state->reps,
                    'lapses' => $state->lapses,
                    'intervals' => $this->reviews->previewIntervals($user, $card, snapshot: $state->toSnapshot()),
                ];
            });

            return response()->json([
                'cards' => $cards,
                'deck' => [
                    'id' => $staticDeck->id,
                    'name' => $staticDeck->name,
                    'cards_per_day' => $cardsPerDay,
                    'cards_loaded' => $cards->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load cards'], 500);
        }
    }

    /**
     * Update a static card after study
     */
    public function updateCard(Request $request, StaticCard $card): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:4',
            'review_duration_ms' => 'nullable|integer|min:0',
            'client_uuid' => 'nullable|uuid',
        ]);

        try {
            $user = $request->user();

            $outcome = $this->reviews->reviewStaticCard(
                user: $user,
                card: $card,
                rating: Rating::from((int) $validated['rating']),
                durationMs: $validated['review_duration_ms'] ?? null,
                clientUuid: $validated['client_uuid'] ?? null,
            );

            $this->touchProgress($user, (int) $card->static_deck_id, 1);

            return response()->json([
                'success' => true,
                'card' => [
                    'id' => $card->id,
                    'state' => $outcome->state->value,
                    'stability' => $outcome->stability,
                    'difficulty' => $outcome->difficulty,
                    'due' => $outcome->due,
                    'scheduled_days' => $outcome->scheduledDays,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update card'], 500);
        }
    }

    /**
     * Batch update static cards
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*.card_id' => 'required|integer|exists:static_cards,id',
            'updates.*.rating' => 'required|integer|min:1|max:4',
            'updates.*.review_duration_ms' => 'nullable|integer|min:0',
            'updates.*.client_uuid' => 'nullable|uuid',
        ]);

        try {
            $user = $request->user();
            $cards = StaticCard::with('staticDeck')
                ->findMany(array_column($validated['updates'], 'card_id'))
                ->keyBy('id');

            $studiedPerDeck = [];

            foreach ($validated['updates'] as $update) {
                $card = $cards->get($update['card_id']);
                if (! $card) {
                    continue;
                }

                $this->reviews->reviewStaticCard(
                    user: $user,
                    card: $card,
                    rating: Rating::from((int) $update['rating']),
                    durationMs: $update['review_duration_ms'] ?? null,
                    clientUuid: $update['client_uuid'] ?? null,
                );

                $studiedPerDeck[(int) $card->static_deck_id][$card->id] = true;
            }

            foreach ($studiedPerDeck as $deckId => $cardIds) {
                $this->touchProgress($user, (int) $deckId, count($cardIds));
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update cards'], 500);
        }
    }

    /**
     * How many cards this user studies per day in a deck.
     */
    private function cardsPerDayFor(User $user, StaticDeck $staticDeck): int
    {
        return (int) (UserStaticDeckSetting::where('user_id', $user->id)
            ->where('static_deck_id', $staticDeck->id)
            ->value('cards_per_day') ?? 10);
    }

    /**
     * Cards this user still owes work on, in queue order.
     *
     * A card with no state row has never been seen, so it counts as new and sorts
     * after everything already in progress -- the same ordering personal decks use.
     *
     * @return Collection<int, StaticCard>
     */
    private function dueCardsFor(User $user, StaticDeck $staticDeck, int $limit): Collection
    {
        $cardIds = $staticDeck->cards()->select('id');

        $dueIds = UserStaticCardState::query()
            ->where('user_id', $user->id)
            ->whereIn('static_card_id', $cardIds)
            ->due()
            ->queueOrder()
            ->pluck('static_card_id');

        $seenIds = UserStaticCardState::query()
            ->where('user_id', $user->id)
            ->whereIn('static_card_id', $cardIds)
            ->pluck('static_card_id');

        $unseenIds = $staticDeck->cards()
            ->whereNotIn('id', $seenIds)
            ->orderBy('id')
            ->pluck('id');

        $ordered = $dueIds->concat($unseenIds)->take($limit);

        if ($ordered->isEmpty()) {
            return new Collection;
        }

        $cards = StaticCard::whereIn('id', $ordered)->get()->keyBy('id');

        return new Collection(
            $ordered->map(fn ($id) => $cards->get($id))->filter()->values()->all()
        );
    }

    /**
     * Advance the user's deck-level progress counter.
     */
    private function touchProgress(User $user, int $staticDeckId, int $studied): void
    {
        $progress = UserStaticDeckProgress::firstOrCreate(
            ['user_id' => $user->id, 'static_deck_id' => $staticDeckId],
            ['cards_studied' => 0, 'total_cards' => StaticCard::where('static_deck_id', $staticDeckId)->count()],
        );

        $progress->updateProgress(
            $progress->cards_studied + $studied,
            [
                'last_session_cards' => $studied,
                'last_session_date' => now()->toDateString(),
            ]
        );
    }
}
