<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\UserStaticDeckProgress
 *
 * @property positive-int $id
 * @property positive-int $user_id
 * @property positive-int $static_deck_id
 * @property int $cards_studied
 * @property int $total_cards
 * @property \Carbon\Carbon|null $last_studied_at
 * @property \Carbon\Carbon|null $completed_at
 * @property array|null $progress_data
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\StaticDeck $staticDeck
 * @property-read \App\Models\User $user
 */
class UserStaticDeckProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'static_deck_id',
        'cards_studied',
        'total_cards',
        'last_studied_at',
        'completed_at',
        'progress_data',
    ];

    protected function casts(): array
    {
        return [
            'last_studied_at' => 'datetime',
            'completed_at' => 'datetime',
            'progress_data' => 'array',
        ];
    }

    /**
     * Get the user that owns this progress
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the static deck for this progress
     */
    public function staticDeck(): BelongsTo
    {
        return $this->belongsTo(StaticDeck::class);
    }

    /**
     * Check if the deck is completed
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentage(): float
    {
        if ($this->total_cards === 0) {
            return 0;
        }

        return ($this->cards_studied / $this->total_cards) * 100;
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'completed_at' => now(),
            'cards_studied' => $this->total_cards,
        ]);
    }

    /**
     * Update progress
     */
    public function updateProgress(int $cardsStudied, ?array $progressData = null): void
    {
        $this->update([
            'cards_studied' => $cardsStudied,
            'last_studied_at' => now(),
            'progress_data' => $progressData,
        ]);

        // Auto-complete if all cards are studied
        if ($cardsStudied >= $this->total_cards && ! $this->isCompleted()) {
            $this->markAsCompleted();
        }
    }

    /**
     * Record a study session against a user's progress in a lesson.
     *
     * `$firstReviews` counts cards seen for the first time, not ratings:
     * counting every rating marked a 50-card lesson "Completed!" after reviewing
     * the same 10 cards five times, and pushed the bar past 100%. Shared by the
     * online study endpoints and the offline sync, which replays the same
     * reviews later.
     */
    public static function recordStudied(User $user, int $staticDeckId, int $firstReviews): void
    {
        $progress = self::firstOrCreate(
            ['user_id' => $user->id, 'static_deck_id' => $staticDeckId],
            ['cards_studied' => 0, 'total_cards' => StaticCard::where('static_deck_id', $staticDeckId)->count()],
        );

        $progress->updateProgress(
            min($progress->cards_studied + $firstReviews, max($progress->total_cards, $progress->cards_studied)),
            [
                'last_session_cards' => $firstReviews,
                'last_session_date' => now()->toDateString(),
            ]
        );
    }
}
