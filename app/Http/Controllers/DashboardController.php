<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Deck;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\View\View
    {
        $user = Auth::user();

        $decks = Deck::query()->where('user_id', $user->id)
            ->withCount('cards')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalCards = $decks->sum('cards_count');

        $cardsToday = 0;

        return view('pages.dashboard', compact('decks', 'totalCards', 'cardsToday'));
    }
}
