<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Deck;
use App\Policies\DeckPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Deck::class, DeckPolicy::class);
    }
}
