<?php

namespace App\Models;

use App\Enums\ChecklistInstanceStatus;
use App\Support\Search\LikePattern;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'checklist_template_id',
        'auditor_id',
        'status',
        'current_version',
        'started_at',
        'submitted_at',
        'finalized_at',
    ];

    protected $casts = [
        'status' => ChecklistInstanceStatus::class,
        'current_version' => 'integer',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id')
            ->withTrashed();
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ChecklistAnswer::class);
    }

    /**
     * Search checklist instances by template name or status value (partial match).
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $pattern = LikePattern::wrap($term);
        if ($pattern === null) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($pattern) {
            $q->whereHas('template', fn (Builder $t) => $t->withTrashed()->where('name', 'like', $pattern))
                ->orWhere('status', 'like', $pattern);
        });
    }
}
