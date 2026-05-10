<?php

namespace App\Http\Controllers\Api;

use App\Enums\ExportDetailLevel;
use App\Enums\ExportType;
use App\Http\Controllers\Api\Concerns\ApiResponses;
use App\Http\Controllers\Concerns\ResolvesPerPage;
use App\Http\Controllers\Controller;
use App\Models\Export;
use App\Services\Exports\ExportPdfCoordinator;
use App\Support\Search\LikePattern;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ExportController extends Controller
{
    use ApiResponses;
    use ResolvesPerPage;

    public function __construct(private readonly ExportPdfCoordinator $coordinator) {}

    public function store(Request $request): JsonResponse|SymfonyResponse
    {
        $request->validate([
            'export_type' => ['required', Rule::enum(ExportType::class)],
        ]);

        $type = ExportType::from($request->string('export_type')->toString());

        $filters = match ($type) {
            ExportType::ChecklistInstance => $this->validatedChecklistInstanceFilters($request),
            ExportType::ChecklistReport => $this->validatedChecklistReportFilters($request),
            ExportType::ChecklistTemplate => $this->validatedChecklistTemplateFilters($request),
            ExportType::ComplianceSnapshot => $this->validatedComplianceSnapshotFilters($request),
            ExportType::AuditorActivity => $this->validatedAuditorActivityFilters($request),
        };

        return $this->coordinator->respondApiExport($request->user(), $type, $filters);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Export::class);

        $perPage = $this->resolvePerPage($request);
        $search = $request->input('search');

        $query = Export::query()->orderByDesc('id');

        if (! ($request->user()->hasRole('admin') && $request->boolean('all'))) {
            $query->where('user_id', $request->user()->id);
        }

        if (is_string($search) && trim($search) !== '') {
            $pattern = LikePattern::wrap($search);
            if ($pattern !== null) {
                $query->where(function ($q) use ($pattern) {
                    $q->where('export_type', 'like', $pattern)
                        ->orWhere('status', 'like', $pattern)
                        ->orWhere('original_filename', 'like', $pattern);
                });
            }
        }

        $paginator = $query->paginate($perPage)->withQueryString();

        $items = collect($paginator->items())->map(fn (Export $e) => $this->exportSummary($e))->values()->all();

        return $this->success([
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ], 'OK');
    }

    public function show(Request $request, Export $export): JsonResponse
    {
        $this->authorize('view', $export);

        return $this->success($this->exportDetail($export), 'OK');
    }

    /**
     * @return array<string, mixed>
     */
    private function exportSummary(Export $export): array
    {
        $row = [
            'uuid' => $export->uuid,
            'export_type' => $export->export_type->value,
            'status' => $export->status->value,
            'created_at' => $export->created_at?->toIso8601String(),
            'completed_at' => $export->completed_at?->toIso8601String(),
        ];

        if ($export->status->value === 'completed' && $export->hasStoredFile()) {
            $row['download_url'] = URL::temporarySignedRoute(
                'exports.download',
                now()->addHour(),
                ['export' => $export->uuid],
            );
        }

        return $row;
    }

    /**
     * @return array<string, mixed>
     */
    private function exportDetail(Export $export): array
    {
        $data = [
            'uuid' => $export->uuid,
            'export_type' => $export->export_type->value,
            'status' => $export->status->value,
            'filters' => $export->filters,
            'created_at' => $export->created_at?->toIso8601String(),
            'completed_at' => $export->completed_at?->toIso8601String(),
            'filename' => $export->original_filename,
            'error' => $export->error_message,
            'download_url' => null,
        ];

        if ($export->status->value === 'completed' && $export->hasStoredFile()) {
            $data['download_url'] = URL::temporarySignedRoute(
                'exports.download',
                now()->addHour(),
                ['export' => $export->uuid],
            );
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedChecklistInstanceFilters(Request $request): array
    {
        return $request->validate([
            'filters' => ['required', 'array'],
            'filters.checklist_instance_id' => ['required', 'integer', 'exists:checklist_instances,id'],
            'filters.detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
            'filters.sections' => ['nullable', 'string', 'max:500'],
        ])['filters'];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedChecklistReportFilters(Request $request): array
    {
        return $request->validate([
            'filters' => ['required', 'array'],
            'filters.date_from' => ['nullable', 'date'],
            'filters.date_to' => ['nullable', 'date', 'after_or_equal:filters.date_from'],
            'filters.template_id' => ['nullable', 'integer', 'exists:checklist_templates,id'],
            'filters.auditor_id' => ['nullable', 'integer', 'exists:users,id'],
            'filters.q' => ['nullable', 'string', 'max:255'],
            'filters.detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
        ])['filters'];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedChecklistTemplateFilters(Request $request): array
    {
        return $request->validate([
            'filters' => ['required', 'array'],
            'filters.checklist_template_id' => ['required', 'integer', 'exists:checklist_templates,id'],
            'filters.detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
        ])['filters'];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedComplianceSnapshotFilters(Request $request): array
    {
        return $request->validate([
            'filters' => ['required', 'array'],
            'filters.date_from' => ['nullable', 'date'],
            'filters.date_to' => ['nullable', 'date', 'after_or_equal:filters.date_from'],
            'filters.detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
        ])['filters'];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedAuditorActivityFilters(Request $request): array
    {
        return $request->validate([
            'filters' => ['required', 'array'],
            'filters.date_from' => ['nullable', 'date'],
            'filters.date_to' => ['nullable', 'date', 'after_or_equal:filters.date_from'],
            'filters.auditor_id' => ['nullable', 'integer', 'exists:users,id'],
            'filters.detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
            'filters.auditor_scope' => ['nullable', 'string', Rule::in(['admin', 'self'])],
        ])['filters'];
    }
}
