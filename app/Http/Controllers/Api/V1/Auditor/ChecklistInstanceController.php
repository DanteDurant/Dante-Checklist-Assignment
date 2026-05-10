<?php

namespace App\Http\Controllers\Api\V1\Auditor;

use App\Application\Assessments\Services\ChecklistCompletionService;
use App\Http\Controllers\Concerns\ResolvesPerPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auditor\CompleteChecklistInstanceRequest;
use App\Http\Requests\Api\V1\Auditor\SaveChecklistProgressRequest;
use App\Http\Requests\Api\V1\Auditor\StartChecklistInstanceRequest;
use App\Http\Resources\Api\V1\Auditor\ChecklistInstanceResource;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChecklistInstanceController extends Controller
{
    use ResolvesPerPage;

    public function __construct(private readonly ChecklistCompletionService $service) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $this->resolvePerPage($request);
        $search = $request->input('search');

        $instances = ChecklistInstance::query()
            ->where('auditor_id', $user->id)
            ->when(is_string($search) && trim($search) !== '', fn ($q) => $q->search($search))
            ->with(['template:id,name'])
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => ChecklistInstanceResource::collection($instances->items()),
            'meta' => [
                'current_page' => $instances->currentPage(),
                'per_page' => $instances->perPage(),
                'total' => $instances->total(),
                'last_page' => $instances->lastPage(),
            ],
        ]);
    }

    public function store(StartChecklistInstanceRequest $request): ChecklistInstanceResource
    {
        $template = ChecklistTemplate::query()->findOrFail($request->integer('template_id'));

        $instance = $this->service->startInstance($template, $request->user());

        return new ChecklistInstanceResource($instance);
    }

    public function show(ChecklistInstance $instance): ChecklistInstanceResource
    {
        $this->authorize('view', $instance);

        $instance->load([
            'answers' => fn ($q) => $q
                ->where('version', $instance->current_version)
                ->orderBy('checklist_question_id'),
        ]);

        return new ChecklistInstanceResource($instance);
    }

    public function saveProgress(SaveChecklistProgressRequest $request, ChecklistInstance $instance): JsonResponse
    {
        $this->service->saveProgress($instance, $request->validated('answers'));

        return response()->json(['message' => 'Saved']);
    }

    public function complete(CompleteChecklistInstanceRequest $request, ChecklistInstance $instance): ChecklistInstanceResource
    {
        $instance = $this->service->complete($instance);

        return new ChecklistInstanceResource($instance);
    }
}
