<?php

declare(strict_types=1);

namespace App\Models;

use App\Fsrs\CardState;
use App\Models\Concerns\HasFsrsMemory;
use Carbon\Carbon;
use Database\Factories\UserStaticCardStateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\UserStaticCardState
 *
 * One learner's FSRS memory of one shared curriculum card.
 *
 * @property positive-int $id
 * @property positive-int $user_id
 * @property positive-int $static_card_id
 * @property CardState $state
 * @property int|null $step
 * @property float|null $stability
 * @property float|null $difficulty
 * @property Carbon|null $due
 * @property Carbon|null $last_review
 * @property int $reps
 * @property int $lapses
 * @property bool $suspended
 * @property-read User $user
 * @property-read StaticCard $staticCard
 */
class UserStaticCardState extends Model
{
    /** @use HasFactory<UserStaticCardStateFactory> */
    use HasFactory;

    use HasFsrsMemory;

    /**
     * Model-level defaults, not just database defaults.
     *
     * A row created without these would come back with `state` null in memory
     * until refreshed, and reading ->state->value on it is a fatal error. The
     * column default only applies inside the database.
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
        'user_id',
        'static_card_id',
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

    protected function casts(): array
    {
        return $this->fsrsCasts();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<StaticCard, $this>
     */
    public function staticCard(): BelongsTo
    {
        return $this->belongsTo(StaticCard::class);
    }

    /**
     * @param  Builder<UserStaticCardState>  $query
     */
    public function scopeDue(Builder $query, ?\DateTimeInterface $at = null): void
    {
        $query->where('suspended', false)
            ->where(function (Builder $q) use ($at): void {
                $q->whereNull('due')->orWhere('due', '<=', $at ?? now());
            });
    }

    /**
     * @param  Builder<UserStaticCardState>  $query
     */
    public function scopeQueueOrder(Builder $query): void
    {
        $query->orderByRaw("CASE state WHEN 'learning' THEN 0 WHEN 'relearning' THEN 0 WHEN 'review' THEN 1 ELSE 2 END")
            ->orderByRaw('due IS NULL')
            ->orderBy('due')
            ->orderBy('id');
    }
}
