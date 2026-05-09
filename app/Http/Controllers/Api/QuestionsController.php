<?php

namespace App\Http\Controllers\Api;

use App\Application\ChecklistTemplates\Services\ChecklistQuestionService;
use App\Http\Controllers\Api\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChecklistTemplates\StoreChecklistQuestionRequest;
use App\Http\Requests\Api\V1\ChecklistTemplates\UpdateChecklistQuestionRequest;
use App\Http\Resources\Api\V1\ChecklistTemplates\ChecklistQuestionResource;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use Illuminate\Http\JsonResponse;

class QuestionsController extends Controller
{
    use ApiResponses;

    public function __construct(private readonly ChecklistQuestionService $service)
    {
    }

    public function store(StoreChecklistQuestionRequest $request, ChecklistTemplate $template): JsonResponse
    {
        $question = $this->service->create($template, $request->validated());

        return $this->success((new ChecklistQuestionResource($question))->resolve(), 'Question created', 201);
    }

    public function update(UpdateChecklistQuestionRequest $request, ChecklistQuestion $question): JsonResponse
    {
        $question = $this->service->update($question, $request->validated());

        return $this->success((new ChecklistQuestionResource($question))->resolve(), 'Question updated', 200);
    }

    public function destroy(ChecklistQuestion $question): JsonResponse
    {
        $this->authorize('update', $question->template);

        $this->service->delete($question);

        return $this->success(null, 'Question deleted', 200);
    }
}

