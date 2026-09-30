<?php

declare(strict_types=1);

namespace App\Models;

use App\Fsrs\CardState;
use App\Models\Concerns\HasFsrsMemory;
use Carbon\Carbon;
use Database\Factories\CardFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * App\Models\Card
 *
 * A card in a personal deck. Memory state lives on this row because the deck
 * belongs to exactly one user.
 *
 * @property positive-int $id
 * @property positive-int $deck_id
 * @property string $front
 * @property string $back
 * @property string|null $description
 * @property array|null $audio
 * @property CardState $state
 * @property int|null $step
 * @property float|null $stability
 * @property float|null $difficulty
 * @property Carbon|null $due
 * @property Carbon|null $last_review
 * @property int $reps
 * @property int $lapses
 * @property bool $suspended
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Deck $deck
 */
class Card extends Model
{
    /** @use HasFactory<CardFactory> */
    use HasFactory;

    use HasFsrsMemory;

    /**
     * Model-level defaults, not just database defaults.
     *
     * Without these a freshly created card has `state` null in memory until it is
     * refreshed, and reading ->state->value on it is a fatal error. The column
     * default only applies inside the database.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'state' => 'new',
        'reps' => 0,
        'lapses' => 0,
        'suspended' => false,
    ];

    protected $fillable = [
        'deck_id',
        'front',
        'back',
        'description',
        'audio',
        'state',
        'step',
        'stability',
        'difficulty',
        'due',
        'last_review',
        'reps',
        'lapses',
        'suspended',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['audio' => 'array'] + $this->fsrsCasts();
    }

    /**
     * The deck this card belongs to
     *
     * @return BelongsTo<Deck, $this>
     */
    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    /**
     * @return MorphMany<ReviewLog, $this>
     */
    public function reviewLogs(): MorphMany
    {
        return $this->morphMany(ReviewLog::class, 'reviewable');
    }

    /**
     * Cards ready to be studied: never scheduled, or due on or before $at.
     *
     * @param  Builder<Card>  $query
     */
    public function scopeDue(Builder $query, ?\DateTimeInterface $at = null): void
    {
        $query->where('suspended', false)
            ->where(function (Builder $q) use ($at): void {
                $q->whereNull('due')->orWhere('due', '<=', $at ?? now());
            });
    }

    /**
     * Learning and relearning cards first, then the most overdue.
     *
     * @param  Builder<Card>  $query
     */
    public function scopeQueueOrder(Builder $query): void
    {
        $query->orderByRaw("CASE state WHEN 'learning' THEN 0 WHEN 'relearning' THEN 0 WHEN 'review' THEN 1 ELSE 2 END")
            ->orderByRaw('due IS NULL')
            ->orderBy('due')
            ->orderBy('id');
    }
}
