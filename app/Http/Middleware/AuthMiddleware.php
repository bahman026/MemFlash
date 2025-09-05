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
        // If user is not authenticated, redirect to login
        if (! Auth::check()) {
            return redirect()->route('login.page');
        }

        return $next($request);
    }
}
