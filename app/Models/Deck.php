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
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'new_cards_per_day' => 'integer',
        ];
    }

    /**
     * The owner of the deck (user who created it)
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All cards in this deck
     *
     * @return HasMany<Card, $this>
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Check if the deck has reached its maximum card limit
     *
     * Compares against DeckLimits::USER_DECK_MAX_CARDS. This used to read a
     * `max_cards` column that no relevant migration ever created, so the value
     * was always null -- making `count() >= null` evaluate as `count() >= 0`,
     * i.e. permanently true, which blocked card creation on every deck.
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
     * Reset spaced-repetition scheduling for every card in this deck.
     *
     * Counterpart to StaticDeck::resetLearningProgress(). DeckController::reset()
     * has always called this method, but it was never implemented -- so
     * POST /decks/{deck}/reset threw BadMethodCallException (HTTP 500).
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
