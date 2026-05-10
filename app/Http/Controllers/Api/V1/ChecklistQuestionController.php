<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\ChecklistTemplates\Services\ChecklistQuestionService;
use App\Http\Controllers\Concerns\ResolvesPerPage;
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
    use ResolvesPerPage;

    public function __construct(private readonly ChecklistQuestionService $service) {}

    public function index(Request $request, ChecklistTemplate $template): JsonResponse
    {
        $this->authorize('view', $template);

        $perPage = $this->resolvePerPage(
            $request,
            50,
            (int) config('list.max_per_page_questions', 200)
        );
        $search = $request->input('search');

        $paginator = $this->service->paginate(
            $template,
            $perPage,
            is_string($search) ? $search : null
        );

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

    public function show(ChecklistQuestion $question): ChecklistQuestionResource
    {
        $this->authorize('view', $question->template);

        return new ChecklistQuestionResource($question);
    }

    public function update(UpdateChecklistQuestionRequest $request, ChecklistQuestion $question): ChecklistQuestionResource
    {
        $question = $this->service->update($question, $request->validated());

        return new ChecklistQuestionResource($question);
    }

    public function destroy(ChecklistQuestion $question): JsonResponse
    {
        $this->authorize('update', $question->template);

        $this->service->delete($question);

        return response()->json([], 204);
    }
}
