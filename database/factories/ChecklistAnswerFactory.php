<?php

namespace Database\Factories;

use App\Models\ChecklistAnswer;
use App\Models\ChecklistInstance;
use App\Models\ChecklistQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChecklistAnswer>
 */
class ChecklistAnswerFactory extends Factory
{
    protected $model = ChecklistAnswer::class;

    public function definition(): array
    {
        return [
            'checklist_instance_id' => ChecklistInstance::factory()->inProgress(),
            'checklist_question_id' => ChecklistQuestion::factory(),
            'version' => 1,
            'value' => ['text' => fake()->sentence()],
            'is_not_applicable' => false,
            'notes' => fake()->optional()->sentence(),
            'answered_at' => now(),
        ];
    }
}

