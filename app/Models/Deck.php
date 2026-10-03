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
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Deck
 *
 * @property positive-int $id
 * @property string $name
 * @property positive-int $user_id
 * @property bool $is_public
 * @property bool $is_default_list
 * @property positive-int $new_cards_per_day
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Card> $cards
 * @property-read int|null $cards_count  only when loaded with withCount('cards')
 * @property-read User $user
 */
class Deck extends Model
{
    /** @use HasFactory<DeckFactory> */
    use HasFactory;

    /**
     * Model-level defaults, not just database defaults.
     *
     * A deck created without them (a word list made from the lookup's list
     * picker, for one) would otherwise hold nulls in memory until refreshed.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_public' => false,
        'is_default_list' => false,
        'new_cards_per_day' => 10,
    ];

    protected $fillable = [
        'name',
        'user_id',
        'is_public',
        'is_default_list',
        'new_cards_per_day',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_default_list' => 'boolean',
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
     * The FSRS scheduling preset for this deck.
     *
     * @return HasOne<DeckConfig, $this>
     */
    public function config(): HasOne
    {
        return $this->hasOne(DeckConfig::class);
    }

    /**
     * The preset, created with the FSRS-6 defaults if it does not exist yet.
     *
     * firstOrCreate rather than create: the `config` relation may already be
     * loaded and cached as null from before the row existed, and a plain create()
     * would then violate the unique index on deck_id. Setting the relation
     * afterwards keeps the in-memory model consistent with the database.
     */
    public function configOrDefault(): DeckConfig
    {
        $config = $this->config ?? $this->config()->firstOrCreate([], DeckConfig::defaults());

        $this->setRelation('config', $config);

        return $config;
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
     * Return every card in this deck to the New state.
     *
     * Memory is cleared, not rewound: FSRS has no meaningful "initial" stability
     * before a first answer, so stability and difficulty go back to null and are
     * derived again from the next rating. Rows in review_logs are left intact --
     * the log is append-only and the optimizer still needs the history.
     */
    public function resetLearningProgress(): void
    {
        $this->cards()->update(Card::forgottenState());
    }
}
