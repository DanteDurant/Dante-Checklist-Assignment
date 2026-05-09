<?php

namespace Database\Seeders\Support;

use App\Enums\ChecklistQuestionType;
use App\Models\ChecklistQuestion;
use Carbon\Carbon;
use Faker\Generator;

final class AnswerValueGenerator
{
    public function __construct(private readonly Generator $faker) {}

    /**
     * @param  array<int, array{value:string,label:string}|mixed>  $options
     * @return list<string>
     */
    private function optionValues(array $options): array
    {
        $out = [];

        foreach ($options as $row) {
            if (is_array($row) && isset($row['value'])) {
                $v = (string) $row['value'];
                if ($v !== '') {
                    $out[] = $v;
                }
            } elseif (! is_array($row) && $row !== null && $row !== '') {
                $out[] = (string) $row;
            }
        }

        return $out;
    }

    /** @param  array<int, mixed>  $questionOptions */
    public function generate(ChecklistQuestion $question, array $questionOptions): array
    {
        return match ($question->type) {
            ChecklistQuestionType::Boolean => ['boolean' => $this->faker->boolean(78)],

            ChecklistQuestionType::Text => [
                'text' => ucfirst($this->faker->words($this->faker->numberBetween(2, 6), true)).'.',
            ],

            ChecklistQuestionType::Textarea => [
                'text' => collect(range(1, $this->faker->numberBetween(2, 4)))
                    ->map(fn () => '• '.$this->faker->sentence())
                    ->implode("\n"),
            ],

            ChecklistQuestionType::Number => ['number' => (float) $this->faker->numberBetween(0, 500)],

            ChecklistQuestionType::Date => ['date' => Carbon::instance(
                $this->faker->dateTimeBetween('-720 days', 'now')
            )->toDateString()],

            ChecklistQuestionType::DateTime => [
                'datetime' => Carbon::instance(
                    $this->faker->dateTimeBetween('-400 days', 'now')
                )->format('Y-m-d\TH:i'),
            ],

            ChecklistQuestionType::Select,
            ChecklistQuestionType::Radio,
            ChecklistQuestionType::SingleSelect => $this->choicePayload($questionOptions),

            ChecklistQuestionType::Checkbox,
            ChecklistQuestionType::MultiSelect => $this->multiChoicePayload($questionOptions),

            ChecklistQuestionType::Email => ['text' => 'compliance.'.$this->faker->unique()->userName().'@'.$this->faker->safeEmailDomain()],
            ChecklistQuestionType::Phone => ['text' => $this->faker->numerify('+1 ###-555-####')],
            ChecklistQuestionType::Url => ['text' => 'https://reports.'.$this->faker->domainName().'/audits/'.$this->faker->uuid()],

            ChecklistQuestionType::Attachment => ['text' => 'Attachment placeholder — '.$this->faker->uuid()],

            default => ['text' => $this->faker->sentence()],
        };
    }

    /** @param  array<int, mixed>  $questionOptions */
    private function choicePayload(array $questionOptions): array
    {
        $values = $this->optionValues($questionOptions);

        if ($values === []) {
            return ['choice' => 'unknown'];
        }

        return ['choice' => $this->faker->randomElement($values)];
    }

    /** @param  array<int, mixed>  $questionOptions */
    private function multiChoicePayload(array $questionOptions): array
    {
        $values = $this->optionValues($questionOptions);

        if ($values === []) {
            return ['choices' => ['noop']];
        }

        shuffle($values);
        $pick = array_slice($values, 0, min(count($values), $this->faker->numberBetween(1, min(3, count($values)))));
        /** @phpstan-ignore-next-line */
        sort($pick);

        return ['choices' => array_values(array_unique(array_map('strval', $pick)))];
    }
}
