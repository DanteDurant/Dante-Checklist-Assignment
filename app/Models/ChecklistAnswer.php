<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_instance_id',
        'checklist_question_id',
        'version',
        'value',
        'is_not_applicable',
        'notes',
        'answered_at',
    ];

    protected $casts = [
        'version' => 'integer',
        'value' => 'array',
        'is_not_applicable' => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(ChecklistInstance::class, 'checklist_instance_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ChecklistQuestion::class, 'checklist_question_id');
    }
}

