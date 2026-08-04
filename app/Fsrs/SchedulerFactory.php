<?php

declare(strict_types=1);

namespace App\Fsrs;

use App\Fsrs\Fuzz\FuzzSource;
use App\Models\Deck;
use App\Models\DeckConfig;
use App\Models\StaticDeck;
use App\Models\User;
use App\Models\UserStaticDeckSetting;

/**
 * Builds a Scheduler for a given deck and user.
 *
 * Presets live in two places -- deck_configs for personal decks, and
 * user_static_deck_settings for shared curriculum decks -- so this is the single
 * point that knows which shape applies. Everything downstream sees a Scheduler.
 */
class SchedulerFactory
{
    /**
     * Per-request memo, keyed by deck and user.
     *
     * Rendering a queue asks for the same scheduler once per card. Without this,
     * resolving a preset costs a query per card -- 100 cards meant 100 redundant
     * lookups. The lifetime is one request, so a preset changed mid-request is not
     * a concern.
     *
     * @var array<string, Scheduler>
     */
    private array $memo = [];

    public function __construct(
        private readonly FuzzSource $fuzz,
    ) {}

    public function forDeck(Deck $deck, User $user): Scheduler
    {
        return $this->memo["personal:{$deck->id}:{$user->id}"] ??= $this->build(
            $deck->configOrDefault()->toSchedulerConfig($user)
        );
    }

    public function forStaticDeck(StaticDeck $staticDeck, User $user): Scheduler
    {
        return $this->memo["static:{$staticDeck->id}:{$user->id}"] ??= $this->build(
            UserStaticDeckSetting::firstOrCreate(
                ['user_id' => $user->id, 'static_deck_id' => $staticDeck->id],
                ['cards_per_day' => 10, 'is_active' => true] + DeckConfig::defaults(),
            )->toSchedulerConfig($user)
        );
    }

    /**
     * Drop the memo. Only needed if a preset is changed and re-read in the same
     * request, which the optimizer job does not do.
     */
    public function forget(): void
    {
        $this->memo = [];
    }

    /**
     * Defaults for a user with no deck in play, e.g. previewing settings.
     */
    public function forUser(User $user): Scheduler
    {
        return $this->build(new SchedulerConfig(
            rolloverHour: (int) $user->rollover_hour,
            timezone: $user->timezone ?: 'UTC',
        ));
    }

    public function build(SchedulerConfig $config): Scheduler
    {
        return new Scheduler($config, new Fsrs($config->parameters), $this->fuzz);
    }
}
