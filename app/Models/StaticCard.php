<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\StaticCard
 *
 * @property positive-int $id
 * @property positive-int $static_deck_id
 * @property string $front
 * @property string $back
 * @property array|null $audio
 * @property int $interval
 * @property float $ease_factor
 * @property int $repetitions
 * @property Carbon|null $revised_at
 * @property Carbon|null $last_reviewed
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read StaticDeck $staticDeck
 */
class StaticCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'static_deck_id',
        'front',
        'back',
        'audio',
        'interval',
        'ease_factor',
        'repetitions',
        'revised_at',
        'last_reviewed',
    ];

    protected function casts(): array
    {
        return [
            'audio' => 'array',
            'ease_factor' => 'decimal:2',
            'revised_at' => 'datetime',
            'last_reviewed' => 'datetime',
        ];
    }

    /**
     * The static deck this card belongs to
     */
    public function staticDeck(): BelongsTo
    {
        return $this->belongsTo(StaticDeck::class);
    }

    /**
     * Check if the card is due for review
     */
    public function isDue(): bool
    {
        if (! $this->revised_at) {
            return true; // New cards are always due
        }

        return $this->revised_at->isPast();
    }

    /**
     * Reset review schedule (for new copy)
     */
    public function resetSchedule(): void
    {
        $this->interval = 1;
        $this->revised_at = null;
        $this->last_reviewed = null;
        $this->save();
    }
}