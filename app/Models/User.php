<?php

declare(strict_types=1);

namespace App\Models;

use App\Constants\DeckLimits;
use App\Enums\UserLevelEnum;
use App\Enums\UserStatusEnum;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
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
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    /**
     * Model-level defaults, not just database defaults.
     *
     * A user created without an explicit level came back with `level` null in
     * memory, because the column default only applies inside the database. That
     * null then reached StaticDeck::scopeByLevel(), which is typed, and any code
     * path touching recommended decks died with a TypeError.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'level' => UserLevelEnum::STARTER->value,
        'status' => 1,
        'timezone' => 'UTC',
        'rollover_hour' => 4,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'level',
        'preferences',
        // Both are set from the admin's user form, which saves with update($data).
        // Left out, "Block" and "verified" were silently dropped on save.
        'status',
        'email_verified_at',
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
     * Who may open the Filament admin panel.
     *
     * Without this contract Filament's own middleware allows every signed-in user
     * when APP_ENV is local and nobody at all otherwise, so the panel returned 403
     * to the admin in production before AdminAccess was even reached.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->email === config('app.admin_email') && ! $this->isBlocked();
    }

    public function isBlocked(): bool
    {
        return $this->status === UserStatusEnum::BLOCK;
    }

    /**
     * Get the decks for the user.
     *
     * @return HasMany<Deck, $this>
     */
    public function decks(): HasMany
    {
        return $this->hasMany(Deck::class);
    }

    /**
     * Get the user's progress on static decks
     *
     * @return HasMany<UserStaticDeckProgress, $this>
     */
    public function staticDeckProgress(): HasMany
    {
        return $this->hasMany(UserStaticDeckProgress::class);
    }

    /**
     * Get the user's per-deck settings for static decks
     *
     * @return HasMany<UserStaticDeckSetting, $this>
     */
    public function staticDeckSettings(): HasMany
    {
        return $this->hasMany(UserStaticDeckSetting::class);
    }

    /**
     * This user's FSRS memory of shared curriculum cards.
     *
     * @return HasMany<UserStaticCardState, $this>
     */
    public function staticCardStates(): HasMany
    {
        return $this->hasMany(UserStaticCardState::class);
    }

    /**
     * @return HasMany<ReviewLog, $this>
     */
    public function reviewLogs(): HasMany
    {
        return $this->hasMany(ReviewLog::class);
    }

    /**
     * Get static decks appropriate for user's level
     */
    public function getRecommendedStaticDecks()
    {
        // Defensive fallback: scopeByLevel is typed, so a user whose level was
        // never set would otherwise crash rather than simply see starter decks.
        return StaticDeck::active()
            ->byLevel($this->level ?? UserLevelEnum::STARTER)
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
