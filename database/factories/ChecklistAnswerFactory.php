<?php

namespace Database\Factories;

use App\Models\ChecklistAnswer;
use App\Models\ChecklistInstance;
use App\Models\ChecklistQuestion;
use Database\Seeders\Support\AnswerValueGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChecklistAnswer>
 */
class ChecklistAnswerFactory extends Factory
{
    protected $model = ChecklistAnswer::class;

    public function definition(): array
    {
        $faker = fake();

        return [
            'checklist_instance_id' => ChecklistInstance::factory()->inProgress(),
            'checklist_question_id' => function (array $attributes): int {
                $instanceId = $attributes['checklist_instance_id'];
                $instance = $instanceId instanceof ChecklistInstance
                    ? $instanceId
                    : ChecklistInstance::query()->findOrFail($instanceId);

                $question = ChecklistQuestion::query()
                    ->where('checklist_template_id', $instance->checklist_template_id)
                    ->inRandomOrder()
                    ->first();

                return $question?->id
                    ?? ChecklistQuestion::factory()->create([
                        'checklist_template_id' => $instance->checklist_template_id,
                    ])->id;
            },
            'version' => 1,
            'value' => function (array $attributes) use ($faker): array {
                $question = ChecklistQuestion::query()->findOrFail($attributes['checklist_question_id']);
                $options = is_array($question->options) ? $question->options : [];

                return (new AnswerValueGenerator($faker))->generate($question, $options);
            },
            'is_not_applicable' => false,
            'notes' => fake()->optional()->sentence(),
            'answered_at' => now(),
        ];
    }
}
