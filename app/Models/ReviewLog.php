<?php

declare(strict_types=1);

namespace App\Models;

use App\Fsrs\CardState;
use App\Fsrs\Rating;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Models\ReviewLog
 *
 * APPEND ONLY. The optimizer replays this table to reconstruct each card's
 * memory history, so a row must never be updated or deleted. Undo appends a
 * compensating entry instead.
 *
 * @property positive-int $id
 * @property positive-int $user_id
 * @property string $reviewable_type
 * @property positive-int $reviewable_id
 * @property Rating $rating
 * @property CardState $state_before
 * @property int $elapsed_days
 * @property float $retrievability_before
 * @property float $stability_after
 * @property float $difficulty_after
 * @property int $scheduled_days
 * @property int|null $review_duration_ms
 * @property Carbon $reviewed_at
 * @property string|null $client_uuid
 * @property bool $synced_offline
 */
class ReviewLog extends Model
{
    /**
     * Only created_at is meaningful; the row is never updated.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'reviewable_type',
        'reviewable_id',
        'rating',
        'state_before',
        'elapsed_days',
        'retrievability_before',
        'stability_after',
        'difficulty_after',
        'scheduled_days',
        'review_duration_ms',
        'reviewed_at',
        'client_uuid',
        'synced_offline',
    ];

    protected function casts(): array
    {
        return [
            'rating' => Rating::class,
            'state_before' => CardState::class,
            'elapsed_days' => 'integer',
            'retrievability_before' => 'float',
            'stability_after' => 'float',
            'difficulty_after' => 'float',
            'scheduled_days' => 'integer',
            'review_duration_ms' => 'integer',
            'reviewed_at' => 'datetime',
            'synced_offline' => 'boolean',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Only the first review of a card on any given day counts toward training;
     * same-day repeats are excluded.
     *
     * @param  Builder<ReviewLog>  $query
     */
    public function scopeForTraining(Builder $query): void
    {
        $query->orderBy('reviewable_type')
            ->orderBy('reviewable_id')
            ->orderBy('reviewed_at');
    }

    /**
     * A success is anything but Again.
     */
    public function wasSuccess(): bool
    {
        return ! $this->rating->isLapse();
    }
}
