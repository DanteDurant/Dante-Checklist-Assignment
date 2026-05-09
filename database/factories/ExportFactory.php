<?php

namespace Database\Factories;

use App\Enums\ExportStatus;
use App\Enums\ExportType;
use App\Models\Export;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Export>
 */
class ExportFactory extends Factory
{
    protected $model = Export::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'export_type' => ExportType::ComplianceSnapshot,
            'status' => ExportStatus::Queued,
            'filters' => [
                'detail' => 'summary',
                'date_from' => null,
                'date_to' => null,
            ],
            'is_inline' => false,
            'disk' => 'exports',
            'relative_path' => null,
            'original_filename' => null,
            'error_message' => null,
            'dedupe_hash' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => ExportStatus::Completed,
            'relative_path' => fake()->uuid().'/export.pdf',
            'original_filename' => 'export.pdf',
            'completed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => ExportStatus::Failed,
            'error_message' => 'Test failure',
            'completed_at' => now(),
        ]);
    }
}
