<?php

namespace App\Models;

use App\Enums\ExportStatus;
use App\Enums\ExportType;
use Database\Factories\ExportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Export extends Model
{
    /** @use HasFactory<ExportFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'export_type',
        'status',
        'filters',
        'is_inline',
        'disk',
        'relative_path',
        'original_filename',
        'error_message',
        'dedupe_hash',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'is_inline' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'export_type' => ExportType::class,
        'status' => ExportStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Export $export): void {
            if ($export->uuid === null || $export->uuid === '') {
                $export->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [ExportStatus::Completed, ExportStatus::Failed], true);
    }

    public function hasStoredFile(): bool
    {
        return $this->relative_path !== null
            && $this->relative_path !== ''
            && ! $this->is_inline;
    }
}
