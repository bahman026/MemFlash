<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and has admin email.
        // config() not env(): with a cached config, .env is not loaded and env()
        // would return null, silently falling back to the hardcoded default.
        $adminEmail = config('app.admin_email');
        if (! auth()->check() || auth()->user()->email !== $adminEmail) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return $next($request);
    }
}
