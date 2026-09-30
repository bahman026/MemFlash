<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Fsrs\CardState;
use App\Fsrs\Rating;
use App\Models\Card;
use App\Models\Deck;
use App\Services\ReviewService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StudyController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ReviewService $reviews,
    ) {}

    /**
     * Start a study session for a deck
     */
    public function start(Deck $deck): Response
    {
        $this->authorize('view', $deck);

        return response()->view('study.session', [
            'deck' => $deck,
        ]);
    }

    /**
     * Get cards for study session.
     *
     * Ordering is decided here, not by the client: learning and relearning cards
     * come first, then review cards by due date, then new cards.
     */
    public function getCards(Request $request, Deck $deck): JsonResponse
    {
        $this->authorize('view', $deck);

        try {
            $limit = $deck->new_cards_per_day ?? 10;
            $user = $request->user();

            // The deck's limit is on NEW cards per study day. One limit() over the
            // whole queue used to cap due reviews as well (10 shown of 50 due, new
            // cards pushed out), while every reload served a fresh batch.
            $reviewsDue = $deck->cards()
                ->due()
                ->where('state', '!=', CardState::New->value)
                ->queueOrder()
                ->limit(ReviewService::MAX_REVIEWS_PER_SESSION)
                ->get();

            $newCards = $deck->cards()
                ->due()
                ->where('state', CardState::New->value)
                ->orderBy('id')
                ->limit($this->reviews->newCardsLeftToday($user, $limit, Card::class, $deck->cards()->select('id')))
                ->get();

            $dueCards = $reviewsDue->concat($newCards);

            // Point every card at THIS deck instance. Without it, previewIntervals()
            // calls loadMissing('deck') per card and each fresh Deck re-queries its
            // config -- 2 extra queries per card, so 200 on a 100-card queue. Sharing
            // one instance makes loadMissing a no-op and lets configOrDefault() cache
            // the preset on the first call.
            $dueCards->each->setRelation('deck', $deck);
            $deck->configOrDefault();

            $cards = $dueCards->map(fn (Card $card): array => [
                'id' => $card->id,
                'front' => $card->front,
                'back' => $card->back,
                'description' => $card->description,
                'audio' => $card->audio,
                'state' => $card->state->value,
                'stability' => $card->stability,
                'difficulty' => $card->difficulty,
                // Derived on read, never stored. Goes through the deck's own
                // scheduler so an optimized deck's parameters are actually used --
                // Card::retrievability() has no deck context and always falls back
                // to the FSRS-6 defaults.
                'retrievability' => $this->reviews->retrievabilityOf($user, $card),
                'due' => $card->due,
                'last_review' => $card->last_review,
                'reps' => $card->reps,
                'lapses' => $card->lapses,
                // What each button would schedule, so the UI needs no second call.
                'intervals' => $this->reviews->previewIntervals($user, $card),
            ]);

            return response()->json([
                'cards' => $cards,
                'deck' => [
                    'id' => $deck->id,
                    'name' => $deck->name,
                    'total_cards' => $deck->cards()->count(),
                    'new_cards_per_day' => $limit,
                    'cards_loaded' => $cards->count(),
                    'desired_retention' => $deck->configOrDefault()->desired_retention,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load cards: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a single card after rating
     */
    public function updateCard(Request $request, Card $card): JsonResponse
    {
        // 'update' rather than 'view': reviewing mutates the card's schedule, and
        // DeckPolicy::view() also passes for public decks owned by someone else.
        $this->authorize('update', $card->deck);

        $validated = $request->validate([
            // 1=Again, 2=Hard, 3=Good, 4=Easy. Hard is a pass, not a failure.
            'rating' => 'required|integer|min:1|max:4',
            'review_duration_ms' => 'nullable|integer|min:0',
            'client_uuid' => 'nullable|uuid',
        ]);

        try {
            $outcome = $this->reviews->reviewCard(
                user: $request->user(),
                card: $card,
                rating: Rating::from((int) $validated['rating']),
                durationMs: $validated['review_duration_ms'] ?? null,
                clientUuid: $validated['client_uuid'] ?? null,
            );

            return response()->json([
                'success' => true,
                'card' => [
                    'id' => $card->id,
                    'state' => $outcome->state->value,
                    'stability' => $outcome->stability,
                    'difficulty' => $outcome->difficulty,
                    'due' => $outcome->due,
                    'last_review' => $outcome->lastReview,
                    'scheduled_days' => $outcome->scheduledDays,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update card: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Batch update multiple cards
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*.card_id' => 'required|integer|exists:cards,id',
            'updates.*.rating' => 'required|integer|min:1|max:4',
            'updates.*.review_duration_ms' => 'nullable|integer|min:0',
            'updates.*.client_uuid' => 'nullable|uuid',
        ]);

        $updates = $validated['updates'];

        // Authorize every card up front, outside the try/catch below.
        // AuthorizationException extends Exception, so authorizing inside the try
        // would swallow a 403 and report it as a generic 500 instead.
        //
        // Validating only `exists:cards,id` proves the card exists, not that the
        // caller owns it -- without this check any authenticated user could
        // rewrite another user's scheduling by guessing card ids.
        $cards = Card::with('deck')
            ->findMany(array_column($updates, 'card_id'))
            ->keyBy('id');

        foreach ($cards as $card) {
            $this->authorize('update', $card->deck);
        }

        try {
            $user = $request->user();
            $updatedCards = [];

            foreach ($updates as $update) {
                $card = $cards->get($update['card_id']);
                if (! $card) {
                    continue;
                }

                $outcome = $this->reviews->reviewCard(
                    user: $user,
                    card: $card,
                    rating: Rating::from((int) $update['rating']),
                    durationMs: $update['review_duration_ms'] ?? null,
                    clientUuid: $update['client_uuid'] ?? null,
                );

                $updatedCards[] = [
                    'id' => $card->id,
                    'state' => $outcome->state->value,
                    'due' => $outcome->due,
                    'last_review' => $outcome->lastReview,
                    'scheduled_days' => $outcome->scheduledDays,
                    // The study screen repeats a card within the session when it is
                    // due again in minutes; `due` itself does not survive JSON as a
                    // date string, so the interval is sent as a number.
                    'scheduled_seconds' => $outcome->scheduledSeconds,
                ];
            }

            return response()->json([
                'success' => true,
                'updated_cards' => $updatedCards,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update cards: ' . $e->getMessage()], 500);
        }
    }
}
