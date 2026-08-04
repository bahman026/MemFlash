<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\StaticCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * App\Models\StaticCard
 *
 * Shared curriculum content. This row holds no memory state at all: stability,
 * difficulty and the due date belong to a specific learner, so they live on
 * user_static_card_states instead.
 *
 * Those columns used to sit here, which meant one user studying a lesson -- or
 * resetting it -- rewrote the schedule every other user saw.
 *
 * @property positive-int $id
 * @property positive-int $static_deck_id
 * @property string $front
 * @property string $back
 * @property array|null $audio
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read StaticDeck $staticDeck
 */
class StaticCard extends Model
{
    /** @use HasFactory<StaticCardFactory> */
    use HasFactory;

    protected $fillable = [
        'static_deck_id',
        'front',
        'back',
        'audio',
    ];

    protected function casts(): array
    {
        return [
            'audio' => 'array',
        ];
    }

    /**
     * The static deck this card belongs to
     *
     * @return BelongsTo<StaticDeck, $this>
     */
    public function staticDeck(): BelongsTo
    {
        return $this->belongsTo(StaticDeck::class);
    }

    /**
     * Per-user memory state for this card.
     *
     * @return HasMany<UserStaticCardState, $this>
     */
    public function states(): HasMany
    {
        return $this->hasMany(UserStaticCardState::class);
    }

    /**
     * @return MorphMany<ReviewLog, $this>
     */
    public function reviewLogs(): MorphMany
    {
        return $this->morphMany(ReviewLog::class, 'reviewable');
    }

    /**
     * This user's memory state, created on first use.
     */
    public function stateFor(User $user): UserStaticCardState
    {
        return $this->states()->firstOrCreate(
            ['user_id' => $user->id],
            UserStaticCardState::forgottenState(),
        );
    }

    /**
     * The IPA pronunciation stored in the audio payload, when present.
     */
    public function pronunciation(): ?string
    {
        return $this->audio['pronunciation'] ?? null;
    }
}
