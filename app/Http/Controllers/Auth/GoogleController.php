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
            // No ->stateless(): redirectToGoogle() puts an OAuth `state` value in the
            // session, and stateless() skipped verifying it on the way back, which
            // removed the CSRF protection on this callback.
            $googleUser = Socialite::driver('google')->user();

            $user = User::query()->firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'password' => bcrypt(uniqid()), // random password
                    'avatar' => $googleUser->getAvatar(),
                    'level' => \App\Enums\UserLevelEnum::STARTER, // Default level
                ]
            );

            // Google has already verified the address, but email_verified_at is not
            // fillable so firstOrCreate() could never set it.
            if ($user->email_verified_at === null) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

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
