<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Models\StaticDeck;
use App\Models\UserStaticDeckSetting;
use App\Models\UserStaticDeckProgress;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\View\View
    {
        $user = Auth::user();

        // Get user's personal decks
        $decks = Deck::query()->where('user_id', $user->id)
            ->withCount('cards')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get static decks for user's level with user settings and progress
        $staticDecks = StaticDeck::query()
            ->where('level', $user->level)
            ->where('is_active', true)
            ->withCount('cards')
            ->with([
                'userSettings' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                },
                'userProgress' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }
            ])
            ->orderBy('lesson_number')
            ->get();

        // Ensure user settings and progress exist for all static decks
        foreach ($staticDecks as $staticDeck) {
            // Create user settings if they don't exist
            if ($staticDeck->userSettings->isEmpty()) {
                UserStaticDeckSetting::create([
                    'user_id' => $user->id,
                    'static_deck_id' => $staticDeck->id,
                    'cards_per_day' => 10, // Default cards per day
                    'is_active' => true,
                ]);
            }

            // Create user progress if it doesn't exist
            if ($staticDeck->userProgress->isEmpty()) {
                UserStaticDeckProgress::create([
                    'user_id' => $user->id,
                    'static_deck_id' => $staticDeck->id,
                    'cards_studied' => 0,
                    'total_cards' => $staticDeck->cards_count,
                    'last_studied_at' => null,
                    'completed_at' => null,
                ]);
            }
        }

        // Reload static decks with all relationships
        $staticDecks = StaticDeck::query()
            ->where('level', $user->level)
            ->where('is_active', true)
            ->withCount('cards')
            ->with([
                'userSettings' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                },
                'userProgress' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }
            ])
            ->orderBy('lesson_number')
            ->get();

        $totalCards = $decks->sum('cards_count');
        $totalStaticCards = $staticDecks->sum('cards_count');

        $cardsToday = 0;

        return view('pages.dashboard', compact('decks', 'staticDecks', 'totalCards', 'totalStaticCards', 'cardsToday'));
    }
}
