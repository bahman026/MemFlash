<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserLevelEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LevelSelectionController extends Controller
{
    /**
     * Show the level selection page
     */
    public function show(): View
    {
        $levels = UserLevelEnum::cases();
        $currentLevel = auth()->user()->level;
        $isChangingLevel = true; // Always show as changing level since they're already logged in

        return view('auth.level-selection', compact('levels', 'currentLevel', 'isChangingLevel'));
    }

    /**
     * Store the selected level
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'level' => ['required', 'string', 'in:' . implode(',', array_column(UserLevelEnum::cases(), 'value'))],
        ]);

        $user = auth()->user();
        $user->update([
            'level' => UserLevelEnum::from($request->input('level')),
        ]);

        return redirect()->route('dashboard')->with('success', 'Your learning level has been updated! You can now access level-appropriate content.');
    }
}
