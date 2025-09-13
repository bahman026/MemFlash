<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserLevelEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\StaticDeck
 *
 * @property positive-int $id
 * @property string $name
 * @property string|null $description
 * @property UserLevelEnum $level
 * @property string|null $category
 * @property string $language
 * @property bool $is_active
 * @property int $sort_order
 * @property array|null $metadata
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserStaticDeckProgress> $userProgress
 */
class StaticDeck extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'level',
        'category',
        'language',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'level' => UserLevelEnum::class,
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * Get all user progress for this static deck
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserStaticDeckProgress::class);
    }

    /**
     * Scope to get decks by level
     */
    public function scopeByLevel($query, UserLevelEnum $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope to get active decks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get decks by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
