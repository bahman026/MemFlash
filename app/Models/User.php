<?php

declare(strict_types=1);

namespace App\Models;

use App\Constants\DeckLimits;
use App\Enums\UserLevelEnum;
use App\Enums\UserStatusEnum;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\User
 *
 * @property positive-int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $avatar
 * @property UserStatusEnum $status
 * @property UserLevelEnum $level
 * @property array|null $preferences
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Deck> $decks
 * @property-read Collection<int, UserStaticDeckProgress> $staticDeckProgress
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'level',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatusEnum::class,
            'level' => UserLevelEnum::class,
            'preferences' => 'array',
        ];
    }

    /**
     * Get the decks for the user.
     */
    public function decks(): HasMany
    {
        return $this->hasMany(Deck::class);
    }

    /**
     * Get the user's progress on static decks
     */
    public function staticDeckProgress(): HasMany
    {
        return $this->hasMany(UserStaticDeckProgress::class);
    }

    /**
     * Get static decks appropriate for user's level
     */
    public function getRecommendedStaticDecks()
    {
        return StaticDeck::active()
            ->byLevel($this->level)
            ->ordered()
            ->get();
    }

    /**
     * Get or create progress for a static deck
     */
    public function getStaticDeckProgress(StaticDeck $staticDeck): UserStaticDeckProgress
    {
        return $this->staticDeckProgress()
            ->firstOrCreate(
                ['static_deck_id' => $staticDeck->id],
                ['total_cards' => 0] // Will be updated when cards are loaded
            );
    }

    /**
     * Check if the user has reached the maximum number of decks
     */
    public function hasReachedDeckLimit(): bool
    {
        return $this->decks()->count() >= DeckLimits::USER_MAX_DECKS;
    }

    /**
     * Get the number of decks remaining before reaching the limit
     */
    public function getRemainingDeckSlots(): int
    {
        return max(0, DeckLimits::USER_MAX_DECKS - $this->decks()->count());
    }

    /**
     * Get the current number of decks created by the user
     */
    public function getDeckCount(): int
    {
        return $this->decks()->count();
    }
}
