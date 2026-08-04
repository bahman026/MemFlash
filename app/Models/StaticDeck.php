<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserLevelEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\StaticDeck
 *
 * @property positive-int $id
 * @property string $name
 * @property string|null $description
 * @property UserLevelEnum $level
 * @property string|null $category
 * @property string $language
 * @property bool $is_active
 * @property int $sort_order
 * @property array|null $metadata
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserStaticDeckProgress> $userProgress
 */
class StaticDeck extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'level',
        'lesson_number',
        'category',
        'language',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'level' => UserLevelEnum::class,
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * Get all cards for this static deck
     *
     * @return HasMany<StaticCard, $this>
     */
    public function cards(): HasMany
    {
        return $this->hasMany(StaticCard::class);
    }

    /**
     * Get all user progress for this static deck
     *
     * @return HasMany<UserStaticDeckProgress, $this>
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserStaticDeckProgress::class);
    }

    /**
     * Get user settings for this static deck
     *
     * @return HasMany<UserStaticDeckSetting, $this>
     */
    public function userSettings(): HasMany
    {
        return $this->hasMany(UserStaticDeckSetting::class);
    }

    /**
     * Scope to get decks by level
     */
    public function scopeByLevel($query, UserLevelEnum $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope to get active decks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get decks by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Reset one user's progress through this deck.
     *
     * Takes a User on purpose. The previous version wrote to the shared
     * static_cards rows, so a single learner pressing "reset" rewound the
     * schedule for every other user of the deck. Memory now lives on
     * user_static_card_states, and only that user's rows are touched.
     *
     * review_logs is append-only and is deliberately left alone.
     *
     * @return int rows affected
     */
    public function resetLearningProgressFor(User $user): int
    {
        return UserStaticCardState::query()
            ->where('user_id', $user->id)
            ->whereIn('static_card_id', $this->cards()->select('id'))
            ->update(UserStaticCardState::forgottenState());
    }

    /**
     * How many cards of this deck the user has memory state for.
     */
    public function startedCountFor(User $user): int
    {
        return UserStaticCardState::query()
            ->where('user_id', $user->id)
            ->whereIn('static_card_id', $this->cards()->select('id'))
            ->where('reps', '>', 0)
            ->count();
    }
}
