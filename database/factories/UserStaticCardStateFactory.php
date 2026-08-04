<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Fsrs\CardState;
use App\Models\StaticCard;
use App\Models\User;
use App\Models\UserStaticCardState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserStaticCardState>
 */
class UserStaticCardStateFactory extends Factory
{
    protected $model = UserStaticCardState::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'static_card_id' => StaticCard::factory(),
            'state' => CardState::New,
            'step' => null,
            'stability' => null,
            'difficulty' => null,
            'due' => now(),
            'last_review' => null,
            'reps' => 0,
            'lapses' => 0,
            'suspended' => false,
        ];
    }

    public function due(float $stability = 10.0, float $difficulty = 5.0): static
    {
        return $this->state(fn (array $attributes): array => [
            'state' => CardState::Review,
            'stability' => $stability,
            'difficulty' => $difficulty,
            'due' => now()->subDay(),
            'last_review' => now()->subDays((int) round($stability)),
            'reps' => 3,
        ]);
    }

    public function notDue(float $stability = 30.0, float $difficulty = 5.0): static
    {
        return $this->state(fn (array $attributes): array => [
            'state' => CardState::Review,
            'stability' => $stability,
            'difficulty' => $difficulty,
            'due' => now()->addWeeks(2),
            'last_review' => now(),
            'reps' => 5,
        ]);
    }
}
