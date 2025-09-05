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
 * @property positive-int $interval
 * @property float $ease
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
        'ease',
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
            'audio' => 'array',             // store audio as JSON
            'last_reviewed' => 'datetime',  // last review timestamp
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
     * Reset review schedule (for new copy)
     */
    public function resetSchedule(): void
    {
        $this->interval = 1;
        $this->ease = 2.5;
        $this->last_reviewed = null;
        $this->save();
    }
}
