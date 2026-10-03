<?php

declare(strict_types=1);

namespace App\Providers;

use App\Fsrs\Fuzz\FuzzSource;
use App\Fsrs\Fuzz\RandomFuzzSource;
use App\Models\Deck;
use App\Policies\DeckPolicy;
use App\Services\WordLookup\WordLookupService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Interval fuzz is injected rather than reached for globally, so tests can
        // make scheduling deterministic and compare against the reference vectors.
        $this->app->bind(FuzzSource::class, RandomFuzzSource::class);

        $this->app->bind(WordLookupService::class, fn (Application $app): WordLookupService => WordLookupService::fromConfig(
            $app['config']->get('services.word_lookup'),
            $app,
            $app['cache']->store(),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Deck::class, DeckPolicy::class);
    }
}
