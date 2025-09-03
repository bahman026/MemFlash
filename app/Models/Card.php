<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Card extends Model
{
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

    protected $casts = [
        'audio' => 'array',             // store audio as JSON
        'last_reviewed' => 'datetime',  // last review timestamp
    ];

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
