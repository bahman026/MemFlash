<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\CardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Card
 *
 * @property positive-int $id
 * @property positive-int $deck_id
 * @property string $front
 * @property string $back
 * @property array|null $audio
 * @property int|null $interval
 * @property Carbon|null $revised_at
 * @property Carbon|null $last_reviewed
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Deck $deck
 */
class Card extends Model
{
    /** @use HasFactory<CardFactory> */
    use HasFactory;

    protected $fillable = [
        'deck_id',
        'front',
        'back',
        'audio',
        'interval',
        'revised_at',
        'last_reviewed',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'audio' => 'array',
            'revised_at' => 'datetime',
            'last_reviewed' => 'datetime',
        ];
    }

    /**
     * The deck this card belongs to
     */
    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    /**
     * Check if the card is due for review
     */
    public function isDue(): bool
    {
        if (!$this->revised_at) {
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
