<?php

declare(strict_types=1);

namespace App\Models;

use App\Constants\DeckLimits;
use Carbon\Carbon;
use Database\Factories\DeckFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Deck
 *
 * @property positive-int $id
 * @property string $name
 * @property positive-int $user_id
 * @property bool $is_public
 * @property positive-int $new_cards_per_day
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Card> $cards
 * @property-read User $user
 */
class Deck extends Model
{
    /** @use HasFactory<DeckFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'is_public',
        'new_cards_per_day',
        'max_cards',
    ];

    /**
     * The owner of the deck (user who created it)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All cards in this deck
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Check if the deck has reached its maximum card limit
     */
    public function hasReachedCardLimit(): bool
    {
        return $this->cards()->count() >= DeckLimits::USER_DECK_MAX_CARDS;
    }

    /**
     * Get the number of cards remaining before reaching the limit
     */
    public function getRemainingCardSlots(): int
    {
        return max(0, DeckLimits::USER_DECK_MAX_CARDS - $this->cards()->count());
    }

    /**
     * Reset learning progress for all cards in this deck
     */
    public function resetLearningProgress(): void
    {
        $this->cards()->update([
            'interval' => 1,
            'ease_factor' => 2.5,
            'repetitions' => 0,
            'revised_at' => null,
            'last_reviewed' => null,
        ]);
    }
}
