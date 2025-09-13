<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\UserStaticDeckSetting
 *
 * @property positive-int $id
 * @property positive-int $user_id
 * @property positive-int $static_deck_id
 * @property int $cards_per_day
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read StaticDeck $staticDeck
 * @property-read User $user
 */
class UserStaticDeckSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'static_deck_id',
        'cards_per_day',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * The user this setting belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The static deck this setting belongs to
     */
    public function staticDeck(): BelongsTo
    {
        return $this->belongsTo(StaticDeck::class);
    }
}
