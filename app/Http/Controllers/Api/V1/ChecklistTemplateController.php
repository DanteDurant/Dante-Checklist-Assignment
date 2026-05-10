<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\ChecklistTemplates\Services\ChecklistTemplateService;
use App\Http\Controllers\Concerns\ResolvesPerPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChecklistTemplates\StoreChecklistTemplateRequest;
use App\Http\Requests\Api\V1\ChecklistTemplates\UpdateChecklistTemplateRequest;
use App\Http\Resources\Api\V1\ChecklistTemplates\ChecklistTemplateResource;
use App\Models\ChecklistTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChecklistTemplateController extends Controller
{
    use ResolvesPerPage;

    public function __construct(private readonly ChecklistTemplateService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ChecklistTemplate::class);

        $perPage = $this->resolvePerPage($request);
        $search = $request->input('search');

        $paginator = $this->service->paginate($perPage, is_string($search) ? $search : null);

        return response()->json([
            'data' => ChecklistTemplateResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function store(StoreChecklistTemplateRequest $request): ChecklistTemplateResource
    {
        $template = $this->service->create($request->validated(), $request->user());

        return new ChecklistTemplateResource($template->loadCount('questions'));
    }

    public function show(ChecklistTemplate $template): ChecklistTemplateResource
    {
        $this->authorize('view', $template);

        return new ChecklistTemplateResource($template->load(['questions' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')]));
    }

    public function update(UpdateChecklistTemplateRequest $request, ChecklistTemplate $template): ChecklistTemplateResource
    {
        $template = $this->service->update($template, $request->validated());

        return new ChecklistTemplateResource($template->loadCount('questions'));
    }

    public function destroy(ChecklistTemplate $template): JsonResponse
    {
        $this->authorize('delete', $template);

        $this->service->delete($template);

        return response()->json([], 204);
    }
}
