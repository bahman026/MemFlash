<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.access' => \App\Http\Middleware\AdminAccess::class,
        ]);

        // TLS ends at the edge proxy, so PHP only ever sees plain HTTP from nginx.
        // Without trusting X-Forwarded-Proto, every generated URL (Vite assets,
        // Livewire, Filament, redirects) comes out as http:// on an https page and
        // the browser blocks it. Private ranges only: the proxy reaches nginx over
        // a Docker network, and nothing public talks to PHP directly.
        $middleware->trustProxies(at: [
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            '127.0.0.1',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
