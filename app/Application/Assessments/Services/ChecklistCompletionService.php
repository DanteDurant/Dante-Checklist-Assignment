<?php

namespace App\Application\Assessments\Services;

use App\Enums\ChecklistInstanceStatus;
use App\Enums\ChecklistTemplateStatus;
use App\Models\ChecklistAnswer;
use App\Models\ChecklistInstance;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChecklistCompletionService
{
    public function startInstance(ChecklistTemplate $template, User $auditor): ChecklistInstance
    {
        if ($template->status !== ChecklistTemplateStatus::Published) {
            throw ValidationException::withMessages([
                'template_id' => ['Template must be published to start an instance.'],
            ]);
        }

        return DB::transaction(function () use ($template, $auditor) {
            $instance = new ChecklistInstance();
            $instance->public_id = (string) Str::ulid();
            $instance->checklist_template_id = $template->id;
            $instance->auditor_id = $auditor->id;
            $instance->status = ChecklistInstanceStatus::InProgress;
            $instance->current_version = 1;
            $instance->started_at = now();
            $instance->save();

            return $instance->fresh();
        });
    }

    /**
     * Save draft progress by upserting answers for the instance current version.
     *
     * @param array<int, array{question_id:int, value?:mixed, is_not_applicable?:bool, notes?:string|null}> $answers
     */
    public function saveProgress(ChecklistInstance $instance, array $answers): void
    {
        $this->assertEditable($instance);

        $questionIds = collect($answers)->pluck('question_id')->unique()->values();

        /** @var Collection<int, ChecklistQuestion> $questions */
        $questions = ChecklistQuestion::query()
            ->where('checklist_template_id', $instance->checklist_template_id)
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        if ($questions->count() !== $questionIds->count()) {
            throw ValidationException::withMessages([
                'answers' => ['One or more questions do not belong to this template.'],
            ]);
        }

        DB::transaction(function () use ($instance, $answers) {
            foreach ($answers as $row) {
                $questionId = (int) $row['question_id'];

                $payload = [
                    'value' => array_key_exists('value', $row) ? $row['value'] : null,
                    'is_not_applicable' => (bool) ($row['is_not_applicable'] ?? false),
                    'notes' => $row['notes'] ?? null,
                    'answered_at' => now(),
                ];

                ChecklistAnswer::query()->updateOrCreate(
                    [
                        'checklist_instance_id' => $instance->id,
                        'checklist_question_id' => $questionId,
                        'version' => $instance->current_version,
                    ],
                    $payload
                );
            }

            if ($instance->status === ChecklistInstanceStatus::Draft) {
                $instance->status = ChecklistInstanceStatus::InProgress;
                $instance->started_at ??= now();
                $instance->save();
            }
        });
    }

    public function complete(ChecklistInstance $instance): ChecklistInstance
    {
        $this->assertEditable($instance);

        return DB::transaction(function () use ($instance) {
            $requiredQuestions = ChecklistQuestion::query()
                ->where('checklist_template_id', $instance->checklist_template_id)
                ->where('is_active', true)
                ->where('is_required', true)
                ->pluck('id');

            $answers = ChecklistAnswer::query()
                ->where('checklist_instance_id', $instance->id)
                ->where('version', $instance->current_version)
                ->get()
                ->keyBy('checklist_question_id');

            $missing = [];

            foreach ($requiredQuestions as $questionId) {
                $answer = $answers->get($questionId);

                if (!$answer) {
                    $missing[] = (int) $questionId;
                    continue;
                }

                if ($answer->is_not_applicable) {
                    // For compliance, required questions cannot be marked N/A by default.
                    $missing[] = (int) $questionId;
                    continue;
                }

                if ($answer->value === null || $answer->value === [] || $answer->value === '') {
                    $missing[] = (int) $questionId;
                }
            }

            if (!empty($missing)) {
                throw ValidationException::withMessages([
                    'missing_required_question_ids' => $missing,
                ]);
            }

            $instance->status = ChecklistInstanceStatus::Submitted;
            $instance->submitted_at = now();
            $instance->save();

            return $instance->fresh();
        });
    }

    private function assertEditable(ChecklistInstance $instance): void
    {
        if (!in_array($instance->status, [ChecklistInstanceStatus::Draft, ChecklistInstanceStatus::InProgress], true)) {
            throw ValidationException::withMessages([
                'status' => ['Checklist instance is not editable in its current status.'],
            ]);
        }
    }
}

