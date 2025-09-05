<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Deck;
use App\Models\User;

class DeckPolicy
{
    /**
     * Determine whether the user can view the deck
     */
    public function view(User $user, Deck $deck): bool
    {
        return $deck->user_id === $user->id || $deck->is_public;
    }

    /**
     * Determine whether the user can update the deck
     */
    public function update(User $user, Deck $deck): bool
    {
        return $deck->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the deck
     */
    public function delete(User $user, Deck $deck): bool
    {
        return $deck->user_id === $user->id;
    }
}
