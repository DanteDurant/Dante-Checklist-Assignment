<?php

namespace App\Http\Controllers\Api;

use App\Application\ChecklistTemplates\Services\ChecklistTemplateService;
use App\Http\Controllers\Api\Concerns\ApiResponses;
use App\Http\Controllers\Concerns\ResolvesPerPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChecklistTemplates\StoreChecklistTemplateRequest;
use App\Http\Requests\Api\V1\ChecklistTemplates\UpdateChecklistTemplateRequest;
use App\Http\Resources\Api\V1\ChecklistTemplates\ChecklistTemplateResource;
use App\Models\ChecklistTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplatesController extends Controller
{
    use ApiResponses;
    use ResolvesPerPage;

    public function __construct(private readonly ChecklistTemplateService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ChecklistTemplate::class);

        $perPage = $this->resolvePerPage($request);
        $search = $request->input('search');

        $paginator = $this->service->paginate($perPage, is_string($search) ? $search : null);

        return $this->success([
            'items' => ChecklistTemplateResource::collection($paginator->items())->resolve(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function store(StoreChecklistTemplateRequest $request): JsonResponse
    {
        $template = $this->service->create($request->validated(), $request->user());

        return $this->success(
            (new ChecklistTemplateResource($template->loadCount('questions')))->resolve(),
            'Template created',
            201
        );
    }

    public function show(ChecklistTemplate $template): JsonResponse
    {
        $this->authorize('view', $template);

        $template->load(['questions' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')]);

        return $this->success((new ChecklistTemplateResource($template))->resolve());
    }

    public function update(UpdateChecklistTemplateRequest $request, ChecklistTemplate $template): JsonResponse
    {
        $template = $this->service->update($template, $request->validated());

        return $this->success(
            (new ChecklistTemplateResource($template->loadCount('questions')))->resolve(),
            'Template updated',
            200
        );
    }

    public function destroy(ChecklistTemplate $template): JsonResponse
    {
        $this->authorize('delete', $template);

        $this->service->delete($template);

        return $this->success(null, 'Template deleted', 200);
    }
}
