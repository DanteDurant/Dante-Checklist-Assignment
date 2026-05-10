<?php

namespace App\Http\Controllers\Api;

use App\Application\Assessments\Services\ChecklistCompletionService;
use App\Application\Reporting\Queries\ChecklistInstanceReportQuery;
use App\Enums\ChecklistQuestionType;
use App\Http\Controllers\Api\Concerns\ApiResponses;
use App\Http\Controllers\Concerns\ResolvesPerPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Checklists\SaveDraftRequest;
use App\Http\Requests\Api\Checklists\StartChecklistRequest;
use App\Http\Requests\Api\V1\Reports\ChecklistInstanceReportRequest;
use App\Http\Resources\Api\V1\Auditor\ChecklistInstanceResource;
use App\Http\Resources\Api\V1\Reports\ChecklistInstanceReportResource;
use App\Models\ChecklistInstance;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ChecklistsController extends Controller
{
    use ApiResponses;
    use ResolvesPerPage;

    public function __construct(
        private readonly ChecklistCompletionService $service,
        private readonly ChecklistInstanceReportQuery $reportQuery,
    ) {}

    public function index(Request $request): JsonResponse
    {
        // Auditor lists their own instances.
        if (! ($request->user()?->hasRole('auditor'))) {
            abort(403);
        }

        $perPage = $this->resolvePerPage($request);
        $search = $request->input('search');

        $paginator = ChecklistInstance::query()
            ->where('auditor_id', $request->user()->id)
            ->when(is_string($search) && trim($search) !== '', fn ($q) => $q->search($search))
            ->with(['template:id,name'])
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return $this->success([
            'items' => ChecklistInstanceResource::collection($paginator->items())->resolve(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function start(StartChecklistRequest $request, ChecklistTemplate $template): JsonResponse
    {
        $instance = $this->service->startInstance($template, $request->user());

        return $this->success((new ChecklistInstanceResource($instance))->resolve(), 'Checklist started', 201);
    }

    public function show(Request $request, ChecklistInstance $checklist): JsonResponse
    {
        $this->authorize('view', $checklist);

        $checklist->load([
            'answers' => fn ($q) => $q
                ->where('version', $checklist->current_version)
                ->orderBy('checklist_question_id'),
        ]);

        return $this->success((new ChecklistInstanceResource($checklist))->resolve());
    }

    public function saveDraft(SaveDraftRequest $request, ChecklistInstance $checklist): JsonResponse
    {
        $payload = $this->buildAnswersPayload($checklist, $request->validated('answers'));
        $this->service->saveProgress($checklist, $payload);

        return $this->success(null, 'Draft saved', 200);
    }

    public function complete(Request $request, ChecklistInstance $checklist): JsonResponse
    {
        $this->authorize('complete', $checklist);

        try {
            $checklist = $this->service->complete($checklist);
        } catch (ValidationException $e) {
            // Let the global API exception handler format it.
            throw $e;
        }

        return $this->success((new ChecklistInstanceResource($checklist))->resolve(), 'Checklist completed successfully', 200);
    }

    /**
     * @param  array<int|string, mixed>  $rawAnswers
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
            if (! $question) {
                continue;
            }

            $value = match ($question->type) {
                ChecklistQuestionType::Boolean => ['boolean' => (bool) $rawValue],
                ChecklistQuestionType::Number => ['number' => ($rawValue === '' || $rawValue === null) ? null : (float) $rawValue],
                ChecklistQuestionType::Date => ['date' => ($rawValue === '' || $rawValue === null) ? null : (string) $rawValue],
                ChecklistQuestionType::DateTime => ['datetime' => ($rawValue === '' || $rawValue === null) ? null : (string) $rawValue],

                ChecklistQuestionType::Select,
                ChecklistQuestionType::Radio,
                ChecklistQuestionType::SingleSelect => ['choice' => ($rawValue === '' || $rawValue === null) ? null : (string) $rawValue],

                ChecklistQuestionType::Checkbox,
                ChecklistQuestionType::MultiSelect => [
                    'choices' => is_array($rawValue)
                        ? array_values(array_filter(array_map('strval', $rawValue), fn ($v) => $v !== ''))
                        : [],
                ],

                ChecklistQuestionType::Textarea,
                ChecklistQuestionType::Text,
                ChecklistQuestionType::Email,
                ChecklistQuestionType::Phone,
                ChecklistQuestionType::Url => ['text' => ($rawValue === null) ? null : (string) $rawValue],

                default => ['raw' => $rawValue],
            };

            $payload[] = [
                'question_id' => $questionId,
                'value' => $value,
            ];
        }

        return $payload;
    }

    public function reports(ChecklistInstanceReportRequest $request): JsonResponse
    {
        $paginator = $this->reportQuery->paginate($request->validated());

        return $this->success([
            'items' => ChecklistInstanceReportResource::collection($paginator->items())->resolve(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}
