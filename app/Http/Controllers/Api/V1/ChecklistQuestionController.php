<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\ChecklistTemplates\Services\ChecklistQuestionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChecklistTemplates\StoreChecklistQuestionRequest;
use App\Http\Requests\Api\V1\ChecklistTemplates\UpdateChecklistQuestionRequest;
use App\Http\Resources\Api\V1\ChecklistTemplates\ChecklistQuestionResource;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChecklistQuestionController extends Controller
{
    public function __construct(private readonly ChecklistQuestionService $service)
    {
    }

    public function index(Request $request, ChecklistTemplate $template): JsonResponse
    {
        $this->authorize('view', $template);

        $perPage = (int) $request->integer('per_page', 50);
        $perPage = max(1, min($perPage, 200));

        $paginator = $this->service->paginate($template, $perPage);

        return response()->json([
            'data' => ChecklistQuestionResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function store(StoreChecklistQuestionRequest $request, ChecklistTemplate $template): ChecklistQuestionResource
    {
        $question = $this->service->create($template, $request->validated());

        return new ChecklistQuestionResource($question);
    }

    public function show(ChecklistTemplate $template, ChecklistQuestion $question): ChecklistQuestionResource
    {
        $this->authorize('view', $template);

        abort_unless($question->checklist_template_id === $template->id, 404);

        return new ChecklistQuestionResource($question);
    }

    public function update(UpdateChecklistQuestionRequest $request, ChecklistTemplate $template, ChecklistQuestion $question): ChecklistQuestionResource
    {
        abort_unless($question->checklist_template_id === $template->id, 404);

        $question = $this->service->update($question, $request->validated());

        return new ChecklistQuestionResource($question);
    }

    public function destroy(ChecklistTemplate $template, ChecklistQuestion $question): JsonResponse
    {
        $this->authorize('update', $template);

        abort_unless($question->checklist_template_id === $template->id, 404);

        $this->service->delete($question);

        return response()->json([], 204);
    }
}

