<?php

namespace App\Models;

use App\Enums\ChecklistTemplateStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistTemplate extends Model
{
    use HasFactory;

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
}

