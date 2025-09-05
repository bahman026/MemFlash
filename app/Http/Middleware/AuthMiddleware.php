<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // List of routes that don't require authentication
        $except = [
            'admin*',
            'auth/*',
            'livewire/*',
            'filament/*',
            'storage/*',
            'login*',
            'up*',
            'test-*',
            'debug-*',
        ];

        // Additional OAuth routes that need to be excluded
        $oauthRoutes = [
            'auth/google',
            'auth/google/callback',
        ];

        if (Auth::check()) {
            return $next($request);
        } elseif (($request->is($except) || in_array($request->path(), $oauthRoutes)) && ! Auth::check()) {
            return $next($request);
        }

        return $next($request);
    }
}
