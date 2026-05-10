<?php

namespace App\Models;

use App\Enums\ChecklistTemplateStatus;
use App\Support\Search\LikePattern;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChecklistTemplate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'public_id',
        'name',
        'description',
        'status',
        'created_by',
        'published_at',
        'archived_at',
    ];

    protected $casts = [
        'status' => ChecklistTemplateStatus::class,
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ChecklistQuestion::class);
    }

    public function instances(): HasMany
    {
        return $this->hasMany(ChecklistInstance::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $pattern = LikePattern::wrap($term);
        if ($pattern === null) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($pattern) {
            $q->where('name', 'like', $pattern)
                ->orWhere('description', 'like', $pattern);
        });
    }
}
