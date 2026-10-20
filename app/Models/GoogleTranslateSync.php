<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\GoogleTranslateSync
 *
 * The Google Sheet a user exported Google Translate's saved words to, and the
 * word list they are synced into. See App\Services\GoogleTranslate\SavedWordsSync.
 *
 * @property positive-int $id
 * @property positive-int $user_id
 * @property positive-int|null $deck_id null = the default list
 * @property string $sheet_url as the user pasted it
 * @property list<string> $imported_words lowercased fronts already brought in
 * @property Carbon|null $last_synced_at
 * @property int $last_added
 * @property string|null $last_error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Deck|null $deck
 */
class GoogleTranslateSync extends Model
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'imported_words' => '[]',
        'last_added' => 0,
    ];

    protected $fillable = [
        'deck_id',
        'sheet_url',
        'imported_words',
        'last_synced_at',
        'last_added',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'imported_words' => 'array',
            'last_synced_at' => 'datetime',
            'last_added' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The list words go into; null means the default list.
     *
     * @return BelongsTo<Deck, $this>
     */
    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }
}
