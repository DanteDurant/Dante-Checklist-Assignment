<?php

namespace Database\Factories;

use App\Enums\ChecklistQuestionType;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ChecklistQuestion>
 */
class ChecklistQuestionFactory extends Factory
{
    protected $model = ChecklistQuestion::class;

    public function definition(): array
    {
        $type = fake()->randomElement([
            ChecklistQuestionType::Boolean,
            ChecklistQuestionType::Text,
            ChecklistQuestionType::Textarea,
            ChecklistQuestionType::Number,
            ChecklistQuestionType::Date,
            ChecklistQuestionType::DateTime,
            ChecklistQuestionType::Select,
            ChecklistQuestionType::Radio,
            ChecklistQuestionType::Checkbox,
            ChecklistQuestionType::SingleSelect,
            ChecklistQuestionType::MultiSelect,
            ChecklistQuestionType::Email,
            ChecklistQuestionType::Phone,
            ChecklistQuestionType::Url,
        ]);

        $options = null;

        if (in_array($type, [
            ChecklistQuestionType::Select,
            ChecklistQuestionType::Radio,
            ChecklistQuestionType::Checkbox,
            ChecklistQuestionType::SingleSelect,
            ChecklistQuestionType::MultiSelect,
        ], true)) {
            $options = collect(range(1, fake()->numberBetween(3, 6)))
                ->map(fn ($i) => [
                    'value' => "option_{$i}",
                    'label' => fake()->words(2, true),
                ])
                ->values()
                ->all();
        }

        return [
            'checklist_template_id' => ChecklistTemplate::factory(),
            'key' => Str::snake(fake()->unique()->words(3, true)),
            'label' => fake()->sentence(),
            'help_text' => fake()->optional()->sentence(),
            'type' => $type,
            'is_required' => fake()->boolean(30),
            'sort_order' => fake()->numberBetween(0, 50),
            'options' => $options,
            'validation' => null,
            'is_active' => true,
        ];
    }
}
