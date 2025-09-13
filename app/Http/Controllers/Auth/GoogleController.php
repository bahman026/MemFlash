<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle(): \Symfony\Component\HttpFoundation\RedirectResponse | \Illuminate\Http\RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::query()->firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'password' => bcrypt(uniqid()), // random password
                    'avatar' => $googleUser->getAvatar(),
                    'level' => \App\Enums\UserLevelEnum::STARTER, // Default level
                ]
            );

            // Check if this is a new user (just created)
            $isNewUser = $user->wasRecentlyCreated;

            // Update avatar if it's different (in case user changed their Google profile picture)
            if ($user->avatar !== $googleUser->getAvatar()) {
                $user->update(['avatar' => $googleUser->getAvatar()]);
            }

            Auth::login($user, true); // true for "remember me"

            // Redirect new users to level selection, existing users to dashboard
            if ($isNewUser) {
                return redirect()->route('level.selection');
            }

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            Log::error('Google OAuth Error: ' . $e->getMessage());

            return redirect()->route('login.page')->with('error', 'Authentication failed. Please try again.');
        }
    }
}
