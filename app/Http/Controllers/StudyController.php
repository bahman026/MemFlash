<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Deck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StudyController extends Controller
{
    use AuthorizesRequests;

    /**
     * Start a study session for a deck
     */
    public function start(Deck $deck): Response
    {
        // Temporarily disable authorization for debugging
        // $this->authorize('view', $deck);
        return response()->view('study.session', [
            'deck' => $deck,
        ]);
    }

    /**
     * Get cards for study session
     */
    public function getCards(Deck $deck): JsonResponse
    {
        // Temporarily disable authorization for debugging
        // $this->authorize('view', $deck);

        try {
            // Get new cards per day from deck settings
            $newCardsPerDay = $deck->new_cards_per_day ?? 10;

            // Get cards that are due for review (revised_at is null or in the past)
            $dueCards = $deck->cards()
                ->where(function ($query) {
                    $query->whereNull('revised_at')
                        ->orWhere('revised_at', '<=', now());
                })
                ->orderByRaw('CASE WHEN revised_at IS NULL THEN 0 ELSE 1 END')
                ->orderBy('revised_at', 'asc') // New cards (null) first, then by due date
                ->limit($newCardsPerDay)
                ->get();

            $cards = $dueCards->map(function ($card) {
                return [
                    'id' => $card->id,
                    'front' => $card->front,
                    'back' => $card->back,
                    'interval' => $card->interval,
                    'revised_at' => $card->revised_at,
                    'last_reviewed' => $card->last_reviewed,
                ];
            });

            $deckInfo = [
                'id' => $deck->id,
                'name' => $deck->name,
                'total_cards' => $deck->cards()->count(),
                'new_cards_per_day' => $newCardsPerDay,
                'cards_loaded' => $cards->count(),
            ];

            return response()->json([
                'cards' => $cards,
                'deck' => $deckInfo,
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
        // Temporarily disable authorization for debugging
        // $this->authorize('view', $card->deck);

        $request->validate([
            'quality' => 'required|integer|min:0|max:3', // 0=Again, 1=Hard, 2=Good, 3=Easy
        ]);

        try {
            $quality = $request->input('quality');
            $now = now();

            // Spaced repetition algorithm
            if ($quality === 0) {
                // Again - reset to 1 day
                $card->update([
                    'interval' => 1,
                    'repetitions' => 0,
                    'ease_factor' => max(1.3, $card->ease_factor - 0.2),
                    'revised_at' => $now->addDay(),
                    'last_reviewed' => $now,
                ]);
            } else {
                // Hard, Good, Easy
                $newInterval = $card->interval;
                $newEaseFactor = $card->ease_factor;
                $newRepetitions = $card->repetitions + 1;

                if ($quality === 1) {
                    // Hard
                    $newInterval = max(1, $card->interval * 1.2);
                    $newEaseFactor = max(1.3, $card->ease_factor - 0.15);
                } elseif ($quality === 2) {
                    // Good
                    if ($card->repetitions === 0) {
                        $newInterval = 1;
                    } elseif ($card->repetitions === 1) {
                        $newInterval = 6;
                    } else {
                        $newInterval = $card->interval * $card->ease_factor;
                    }
                } elseif ($quality === 3) {
                    // Easy
                    if ($card->repetitions === 0) {
                        $newInterval = 4;
                    } elseif ($card->repetitions === 1) {
                        $newInterval = 10;
                    } else {
                        $newInterval = $card->interval * $card->ease_factor;
                    }
                    $newEaseFactor = $card->ease_factor + 0.15;
                }

                $card->update([
                    'interval' => (int) $newInterval,
                    'repetitions' => $newRepetitions,
                    'ease_factor' => $newEaseFactor,
                    'revised_at' => $now->addDays((int) $newInterval),
                    'last_reviewed' => $now,
                ]);
            }

            return response()->json([
                'success' => true,
                'card' => [
                    'id' => $card->id,
                    'revised_at' => $card->revised_at,
                    'last_reviewed' => $card->last_reviewed,
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
        $request->validate([
            'updates' => 'required|array',
            'updates.*.card_id' => 'required|integer|exists:cards,id',
            'updates.*.quality' => 'required|integer|min:0|max:3',
        ]);

        try {
            $updates = $request->input('updates');
            $now = now();
            $updatedCards = [];

            foreach ($updates as $update) {
                $card = Card::find($update['card_id']);
                if (! $card) {
                    continue;
                }

                $quality = $update['quality'];

                // Spaced repetition algorithm
                if ($quality === 0) {
                    // Again - reset to 1 day
                    $card->update([
                        'interval' => 1,
                        'repetitions' => 0,
                        'ease_factor' => max(1.3, $card->ease_factor - 0.2),
                        'revised_at' => $now->addDay(),
                        'last_reviewed' => $now,
                    ]);
                } else {
                    // Hard, Good, Easy
                    $newInterval = $card->interval;
                    $newEaseFactor = $card->ease_factor;
                    $newRepetitions = $card->repetitions + 1;

                    if ($quality === 1) {
                        // Hard
                        $newInterval = max(1, $card->interval * 1.2);
                        $newEaseFactor = max(1.3, $card->ease_factor - 0.15);
                    } elseif ($quality === 2) {
                        // Good
                        if ($card->repetitions === 0) {
                            $newInterval = 1;
                        } elseif ($card->repetitions === 1) {
                            $newInterval = 6;
                        } else {
                            $newInterval = $card->interval * $card->ease_factor;
                        }
                    } elseif ($quality === 3) {
                        // Easy
                        if ($card->repetitions === 0) {
                            $newInterval = 4;
                        } elseif ($card->repetitions === 1) {
                            $newInterval = 10;
                        } else {
                            $newInterval = $card->interval * $card->ease_factor;
                        }
                        $newEaseFactor = $card->ease_factor + 0.15;
                    }

                    $card->update([
                        'interval' => (int) $newInterval,
                        'repetitions' => $newRepetitions,
                        'ease_factor' => $newEaseFactor,
                        'revised_at' => $now->addDays((int) $newInterval),
                        'last_reviewed' => $now,
                    ]);
                }

                $updatedCards[] = [
                    'id' => $card->id,
                    'revised_at' => $card->revised_at,
                    'last_reviewed' => $card->last_reviewed,
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
