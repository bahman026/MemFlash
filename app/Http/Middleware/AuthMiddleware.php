<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // guest() rather than route(): it remembers the page asked for, so
        // signing in returns there. That matters for /lookup?q=word opened from
        // another app, which would otherwise lose the word to the dashboard.
        if (! Auth::check()) {
            return redirect()->guest(route('login.page'));
        }

        // Blocking takes effect on the next request, including for a user still
        // holding a remember-me cookie from before they were blocked.
        if ($request->user()->isBlocked()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login.page')->with('error', 'Your account has been blocked.');
        }

        return $next($request);
    }
}
