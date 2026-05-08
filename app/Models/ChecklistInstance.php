<?php

namespace App\Models;

use App\Enums\ChecklistInstanceStatus;
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
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ChecklistAnswer::class);
    }
}

