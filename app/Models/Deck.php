<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deck extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'is_public',
        'new_cards_per_day',
    ];

    /**
     * The owner of the deck (user who created it)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All cards in this deck
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}
