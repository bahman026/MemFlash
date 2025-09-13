<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\StaticCard;
use App\Models\StaticDeck;
use App\Models\UserStaticDeckProgress;
use App\Models\UserStaticDeckSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StaticDeckController extends Controller
{
    /**
     * Reset learning progress for a static deck
     */
    public function reset(StaticDeck $staticDeck): RedirectResponse
    {
        $user = auth()->user();

        // Reset static cards learning progress
        $staticDeck->resetLearningProgress();

        // Reset user progress
        $userProgress = UserStaticDeckProgress::where('user_id', $user->id)
            ->where('static_deck_id', $staticDeck->id)
            ->first();

        if ($userProgress) {
            $userProgress->update([
                'cards_studied' => 0,
                'last_studied_at' => null,
                'completed_at' => null,
            ]);
        }

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
    public function study(StaticDeck $staticDeck)
    {
        $user = auth()->user();
        $staticDeck->load('cards');

        // Get user's cards per day setting for this deck
        $userSetting = UserStaticDeckSetting::where('user_id', $user->id)
            ->where('static_deck_id', $staticDeck->id)
            ->first();

        $cardsPerDay = $userSetting ? $userSetting->cards_per_day : 10;

        // Get cards that are due for review
        $dueCards = $staticDeck->cards()
            ->where(function ($query) {
                $query->whereNull('revised_at')
                    ->orWhere('revised_at', '<=', now());
            })
            ->orderByRaw('CASE WHEN revised_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('interval')
            ->limit($cardsPerDay)
            ->get();

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

        $user = auth()->user();

        $setting = UserStaticDeckSetting::firstOrCreate(
            [
                'user_id' => $user->id,
                'static_deck_id' => $staticDeck->id,
            ],
            [
                'cards_per_day' => 10,
                'is_active' => true,
            ]
        );

        $setting->update([
            'cards_per_day' => $request->cards_per_day,
        ]);

        return redirect()->back()->with('success', "Cards per day updated to {$request->cards_per_day} for {$staticDeck->name}!");
    }

    /**
     * Get cards for static deck study session
     */
    public function getCards(StaticDeck $staticDeck): JsonResponse
    {
        try {
            // Get cards that are due for review
            $dueCards = $staticDeck->cards()
                ->where(function ($query) {
                    $query->whereNull('revised_at')
                        ->orWhere('revised_at', '<=', now());
                })
                ->orderByRaw('CASE WHEN revised_at IS NULL THEN 0 ELSE 1 END')
                ->orderBy('interval')
                ->get();

            return response()->json([
                'cards' => $dueCards,
                'deck' => [
                    'id' => $staticDeck->id,
                    'name' => $staticDeck->name,
                    'cards_loaded' => $dueCards->count(),
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
        $request->validate([
            'quality' => 'required|integer|min:0|max:3',
        ]);

        try {
            $user = auth()->user();
            $quality = $request->quality;

            // Simple spaced repetition algorithm
            if ($quality === 0) {
                // Again - reset to 1 day
                $card->update([
                    'interval' => 1,
                    'repetitions' => 0,
                    'ease_factor' => max(1.3, $card->ease_factor - 0.2),
                    'revised_at' => now()->addDay(),
                    'last_reviewed' => now(),
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
                    'revised_at' => now()->addDays((int) $newInterval),
                    'last_reviewed' => now(),
                ]);
            }

            // Update user progress
            $userProgress = UserStaticDeckProgress::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'static_deck_id' => $card->static_deck_id,
                ],
                [
                    'cards_studied' => 0,
                    'total_cards' => $card->staticDeck->cards()->count(),
                ]
            );

            // Always increment progress for static cards (they're shared, not per-user)
            $userProgress->updateProgress(
                $userProgress->cards_studied + 1,
                [
                    'last_session_cards' => 1,
                    'last_session_date' => now()->toDateString(),
                ]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update card'], 500);
        }
    }

    /**
     * Batch update static cards
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.card_id' => 'required|integer',
            'updates.*.quality' => 'required|integer|min:0|max:3',
        ]);

        try {
            $user = auth()->user();
            $staticDeckId = null;
            $studiedCardIds = [];

            foreach ($request->updates as $update) {
                $card = StaticCard::find($update['card_id']);
                if ($card) {
                    $staticDeckId = $card->static_deck_id;
                    $quality = $update['quality'];

                    // Track which cards were studied
                    $studiedCardIds[] = $card->id;

                    // Simple spaced repetition algorithm
                    if ($quality === 0) {
                        // Again - reset to 1 day
                        $card->update([
                            'interval' => 1,
                            'repetitions' => 0,
                            'ease_factor' => max(1.3, $card->ease_factor - 0.2),
                            'revised_at' => now()->addDay(),
                            'last_reviewed' => now(),
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
                            'revised_at' => now()->addDays((int) $newInterval),
                            'last_reviewed' => now(),
                        ]);
                    }
                }
            }

            // Update user progress if we have a static deck
            if ($staticDeckId) {
                $staticDeck = StaticDeck::find($staticDeckId);
                if ($staticDeck) {
                    // Get or create user progress
                    $userProgress = UserStaticDeckProgress::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'static_deck_id' => $staticDeckId,
                        ],
                        [
                            'cards_studied' => 0,
                            'total_cards' => $staticDeck->cards()->count(),
                        ]
                    );

                    // Count unique cards studied in this session
                    $uniqueCardsStudied = count(array_unique($studiedCardIds));

                    // Update progress
                    $userProgress->updateProgress(
                        $userProgress->cards_studied + $uniqueCardsStudied,
                        [
                            'last_session_cards' => $uniqueCardsStudied,
                            'last_session_date' => now()->toDateString(),
                        ]
                    );
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update cards'], 500);
        }
    }
}
