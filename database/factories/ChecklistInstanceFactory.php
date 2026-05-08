<?php

namespace Database\Factories;

use App\Enums\ChecklistInstanceStatus;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChecklistInstance>
 */
class ChecklistInstanceFactory extends Factory
{
    protected $model = ChecklistInstance::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'checklist_template_id' => ChecklistTemplate::factory()->published(),
            'auditor_id' => User::factory(),
            'status' => ChecklistInstanceStatus::Draft,
            'current_version' => 1,
            'started_at' => null,
            'submitted_at' => null,
            'finalized_at' => null,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn () => [
            'status' => ChecklistInstanceStatus::InProgress,
            'started_at' => now(),
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn () => [
            'status' => ChecklistInstanceStatus::Submitted,
            'started_at' => now()->subHours(2),
            'submitted_at' => now(),
        ]);
    }
}

