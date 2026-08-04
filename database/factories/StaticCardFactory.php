<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StaticCard;
use App\Models\StaticDeck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaticCard>
 *
 * Static cards hold no memory state: it is per learner and lives on
 * user_static_card_states. Use UserStaticCardStateFactory for scheduling state.
 */
class StaticCardFactory extends Factory
{
    protected $model = StaticCard::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'static_deck_id' => StaticDeck::factory(),
            'front' => $this->faker->word(),
            'back' => $this->faker->word(),
            'audio' => null,
        ];
    }

    /**
     * With an IPA pronunciation, the way the curriculum seeders store it.
     */
    public function withPronunciation(string $ipa = '/wɪn/'): static
    {
        return $this->state(fn (array $attributes): array => [
            'audio' => ['pronunciation' => $ipa],
        ]);
    }
}
