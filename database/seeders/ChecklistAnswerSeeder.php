<?php

namespace Database\Seeders;

use App\Enums\ChecklistInstanceStatus;
use App\Models\ChecklistInstance;
use App\Models\ChecklistQuestion;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Seeders\Support\AnswerValueGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistAnswerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();
        $gen = new AnswerValueGenerator($faker);

        $instances = ChecklistInstance::query()
            ->with([
                'template' => fn ($q) => $q->select(['id', 'name']),
            ])
            ->get();

        /** @var ChecklistInstance $instance */
        foreach ($instances as $instance) {
            $questions = ChecklistQuestion::query()
                ->where('checklist_template_id', $instance->checklist_template_id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($questions->isEmpty()) {
                continue;
            }

            $answers = [];
            $version = $instance->current_version;

            $status = $instance->status;

            foreach ($questions as $question) {
                $options = is_array($question->options) ? $question->options : [];

                if ($status === ChecklistInstanceStatus::Submitted || $status === ChecklistInstanceStatus::Approved) {
                    if ($question->is_required || $faker->boolean(90)) {
                        $answers[] = $this->answerRow(
                            $instance->id,
                            $question->id,
                            $version,
                            $gen->generate($question, $options),
                            $instance->started_at ?? $instance->submitted_at ?? now()
                        );
                    }

                    continue;
                }

                if ($status === ChecklistInstanceStatus::InProgress) {
                    if ($question->is_required) {
                        if ($faker->boolean(88)) {
                            $answers[] = $this->answerRow(
                                $instance->id,
                                $question->id,
                                $version,
                                $gen->generate($question, $options),
                                $instance->started_at ?? now()
                            );
                        }
                    } elseif ($faker->boolean(68)) {
                        $answers[] = $this->answerRow(
                            $instance->id,
                            $question->id,
                            $version,
                            $gen->generate($question, $options),
                            $instance->started_at ?? now()
                        );
                    }

                    continue;
                }

                if ($status === ChecklistInstanceStatus::Draft) {
                    if ($question->is_required && $faker->boolean(55)) {
                        $answers[] = $this->answerRow(
                            $instance->id,
                            $question->id,
                            $version,
                            $gen->generate($question, $options),
                            now()
                        );
                    } elseif (! $question->is_required && $faker->boolean(42)) {
                        $answers[] = $this->answerRow(
                            $instance->id,
                            $question->id,
                            $version,
                            $gen->generate($question, $options),
                            now()
                        );
                    }

                    continue;
                }

                // Rejected or other — light partial history
                if ($faker->boolean(38)) {
                    $answers[] = $this->answerRow(
                        $instance->id,
                        $question->id,
                        $version,
                        $gen->generate($question, $options),
                        $instance->started_at ?? now()
                    );
                }
            }

            foreach (array_chunk($answers, 350) as $chunk) {
                DB::table('checklist_answers')->insert($chunk);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $value
     * @return array<string, mixed>
     */
    private function answerRow(int $instanceId, int $questionId, int $version, array $value, CarbonInterface|\DateTimeInterface|null $answeredAt): array
    {
        $ts = now();

        $answered = $answeredAt instanceof CarbonInterface
            ? $answeredAt->toDateTimeString()
            : ($answeredAt ? Carbon::parse($answeredAt)->toDateTimeString() : null);

        return [
            'checklist_instance_id' => $instanceId,
            'checklist_question_id' => $questionId,
            'version' => $version,
            'value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'is_not_applicable' => false,
            'notes' => null,
            'answered_at' => $answered,
            'created_at' => $ts,
            'updated_at' => $ts,
        ];
    }
}
