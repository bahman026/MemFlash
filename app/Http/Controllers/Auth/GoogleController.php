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
                ]
            );

            // Update avatar if it's different (in case user changed their Google profile picture)
            if ($user->avatar !== $googleUser->getAvatar()) {
                $user->update(['avatar' => $googleUser->getAvatar()]);
            }

            Auth::login($user, true); // true for "remember me"

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            Log::error('Google OAuth Error: ' . $e->getMessage());

            return redirect()->route('login.page')->with('error', 'Authentication failed. Please try again.');
        }
    }
}
