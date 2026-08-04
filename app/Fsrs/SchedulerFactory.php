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
    public function __construct(
        private readonly FuzzSource $fuzz,
    ) {}

    public function forDeck(Deck $deck, User $user): Scheduler
    {
        return $this->build($deck->configOrDefault()->toSchedulerConfig($user));
    }

    public function forStaticDeck(StaticDeck $staticDeck, User $user): Scheduler
    {
        $setting = UserStaticDeckSetting::firstOrCreate(
            ['user_id' => $user->id, 'static_deck_id' => $staticDeck->id],
            ['cards_per_day' => 10, 'is_active' => true] + DeckConfig::defaults(),
        );

        return $this->build($setting->toSchedulerConfig($user));
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
