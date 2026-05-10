<?php

namespace App\Models;

use App\Enums\ChecklistQuestionType;
use App\Support\Search\LikePattern;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_template_id',
        'key',
        'label',
        'help_text',
        'type',
        'is_required',
        'sort_order',
        'options',
        'validation',
        'is_active',
    ];

    protected $casts = [
        'type' => ChecklistQuestionType::class,
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'options' => 'array',
        'validation' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id')
            ->withTrashed();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ChecklistAnswer::class);
    }

    /**
     * Case-insensitive partial match on question label (DB collation dependent).
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $pattern = LikePattern::wrap($term);
        if ($pattern === null) {
            return $query;
        }

        return $query->where('label', 'like', $pattern);
    }
}
