<?php

namespace App\Http\Controllers\Web\Auditor;

use App\Application\Assessments\Services\ChecklistCompletionService;
use App\Enums\ChecklistQuestionType;
use App\Http\Controllers\Controller;
use App\Models\ChecklistAnswer;
use App\Models\ChecklistInstance;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ChecklistInstanceController extends Controller
{
    public function __construct(private readonly ChecklistCompletionService $service)
    {
    }

    /**
     * Start a new checklist instance for the current auditor.
     */
    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'template_id' => ['required', 'integer', 'exists:checklist_templates,id'],
        ]);

        /** @var ChecklistTemplate $template */
        $template = ChecklistTemplate::query()->findOrFail((int) $data['template_id']);

        $instance = $this->service->startInstance($template, $request->user());

        return redirect()
            ->route('auditor.instances.show', $instance)
            ->with('status', 'Checklist started.');
    }

    public function show(ChecklistInstance $instance): \Illuminate\View\View
    {
        $this->authorize('view', $instance);

        $instance->load(['template:id,name']);

        $questions = ChecklistQuestion::query()
            ->where('checklist_template_id', $instance->checklist_template_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $answers = ChecklistAnswer::query()
            ->where('checklist_instance_id', $instance->id)
            ->where('version', $instance->current_version)
            ->get()
            ->keyBy('checklist_question_id');

        return view('auditor.instances.show', [
            'instance' => $instance,
            'questions' => $questions,
            'answers' => $answers,
        ]);
    }

    public function saveDraft(Request $request, ChecklistInstance $instance): RedirectResponse
    {
        $this->authorize('update', $instance);

        $data = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable'],
        ]);

        $payload = $this->buildAnswersPayload($instance, $data['answers'] ?? []);
        $this->service->saveProgress($instance, $payload);

        return redirect()
            ->route('auditor.instances.show', $instance)
            ->with('status', 'Draft saved.');
    }

    public function submit(Request $request, ChecklistInstance $instance): RedirectResponse
    {
        $this->authorize('complete', $instance);

        // Save any changes first (same payload shape as draft).
        $data = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable'],
        ]);

        $payload = $this->buildAnswersPayload($instance, $data['answers'] ?? []);
        $this->service->saveProgress($instance, $payload);

        try {
            $instance = $this->service->complete($instance);
        } catch (ValidationException $e) {
            // Convert service error into per-question inline errors on submit.
            $missing = $e->errors()['missing_required_question_ids'] ?? null;

            if (is_array($missing) && !empty($missing)) {
                $errors = [];
                foreach ($missing as $questionId) {
                    $errors["answers.{$questionId}"] = ['This question is required.'];
                }

                return redirect()
                    ->route('auditor.instances.show', $instance)
                    ->withErrors($errors)
                    ->withInput();
            }

            throw $e;
        }

        return redirect()
            ->route('auditor.instances.show', $instance)
            ->with('status', 'Checklist submitted.');
    }

    /**
     * @param array<int|string, mixed> $rawAnswers
     * @return array<int, array{question_id:int, value:mixed}>
     */
    private function buildAnswersPayload(ChecklistInstance $instance, array $rawAnswers): array
    {
        $questions = ChecklistQuestion::query()
            ->where('checklist_template_id', $instance->checklist_template_id)
            ->where('is_active', true)
            ->get(['id', 'type'])
            ->keyBy('id');

        $payload = [];

        foreach ($rawAnswers as $questionId => $rawValue) {
            $questionId = (int) $questionId;
            $question = $questions->get($questionId);

            if (!$question) {
                continue;
            }

            $value = match ($question->type) {
                ChecklistQuestionType::Boolean => ['boolean' => (bool) $rawValue],
                ChecklistQuestionType::Number => ['number' => ($rawValue === '' || $rawValue === null) ? null : (float) $rawValue],
                ChecklistQuestionType::Text => ['text' => ($rawValue === null) ? null : (string) $rawValue],
                default => ['raw' => $rawValue],
            };

            $payload[] = [
                'question_id' => $questionId,
                'value' => $value,
            ];
        }

        return $payload;
    }
}

