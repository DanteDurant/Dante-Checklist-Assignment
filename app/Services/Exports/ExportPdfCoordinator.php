<?php

namespace App\Services\Exports;

use App\Application\Pdf\PdfExportDocumentFactory;
use App\Application\Pdf\PdfExportService;
use App\Enums\ExportDetailLevel;
use App\Enums\ExportStatus;
use App\Enums\ExportType;
use App\Jobs\GenerateStoredPdfExportJob;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Models\Export;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ExportPdfCoordinator
{
    private const SYNC_REPORT_ROW_CAP = 500;

    public function __construct(
        private readonly PdfExportDocumentFactory $documents,
        private readonly PdfExportService $pdf,
        private readonly ExportQueueDecision $decision,
    ) {}

    public function respondChecklistInstance(Request $request, ChecklistInstance $instance): SymfonyResponse
    {
        Gate::authorize('exportPdf', $instance);

        $validated = $request->validate([
            'detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
            'sections' => ['nullable', 'string', 'max:500'],
        ]);

        $filters = [
            'checklist_instance_id' => $instance->id,
            'detail' => $validated['detail'] ?? null,
            'sections' => $validated['sections'] ?? null,
        ];

        $detail = ExportDetailLevel::fromQuery($filters['detail']);

        if ($this->decision->shouldQueueChecklistInstance($instance, $detail)) {
            return $this->queuedJson($request->user(), ExportType::ChecklistInstance, $filters);
        }

        $doc = $this->documents->documentForChecklistInstance($instance, $filters);

        return $this->pdf->download($doc['view'], $doc['data'], $doc['filename']);
    }

    public function respondCompletedChecklistsReport(Request $request): SymfonyResponse
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'template_id' => ['nullable', 'integer', 'exists:checklist_templates,id'],
            'auditor_id' => ['nullable', 'integer', 'exists:users,id'],
            'q' => ['nullable', 'string', 'max:255'],
            'detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
        ]);

        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        $matched = $this->documents->checklistReportMatchedCount($filters);

        if ($this->decision->shouldQueueChecklistReport($matched, $detail)) {
            return $this->queuedJson($request->user(), ExportType::ChecklistReport, $filters);
        }

        $doc = $this->documents->documentForChecklistReport($filters, self::SYNC_REPORT_ROW_CAP);

        return $this->pdf->download($doc['view'], $doc['data'], $doc['filename']);
    }

    public function respondChecklistTemplate(Request $request, ChecklistTemplate $template): SymfonyResponse
    {
        Gate::authorize('exportPdf', $template);

        $filters = $request->validate([
            'detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
        ]);

        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        $filters['checklist_template_id'] = $template->id;

        if ($this->decision->shouldQueueChecklistTemplate($template, $detail)) {
            return $this->queuedJson($request->user(), ExportType::ChecklistTemplate, $filters);
        }

        $doc = $this->documents->documentForChecklistTemplate($template, $filters);

        return $this->pdf->download($doc['view'], $doc['data'], $doc['filename']);
    }

    public function respondComplianceSnapshot(Request $request): SymfonyResponse
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
        ]);

        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        $instanceCount = $this->decision->snapshotFilteredInstanceCount(
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null,
        );

        $queueSnapshot = $this->decision->shouldQueueComplianceSnapshot($instanceCount, $detail);

        $forceSyncMax = config('pdf_exports.compliance_snapshot_force_sync_max_instances');
        if ($forceSyncMax !== null && (int) $forceSyncMax !== 0 && $instanceCount <= (int) $forceSyncMax) {
            $queueSnapshot = false;
        }

        if ($queueSnapshot) {
            if (config('pdf_exports.log_lifecycle', true)) {
                Log::info('pdf_export.compliance_snapshot.queued', [
                    'user_id' => $request->user()?->id,
                    'instance_count' => $instanceCount,
                    'detail' => $detail->value,
                ]);
            }

            return $this->queuedJson($request->user(), ExportType::ComplianceSnapshot, $filters);
        }

        if (config('pdf_exports.log_lifecycle', true)) {
            Log::info('pdf_export.compliance_snapshot.sync', [
                'user_id' => $request->user()?->id,
                'instance_count' => $instanceCount,
                'detail' => $detail->value,
            ]);
        }

        $doc = $this->documents->documentForComplianceSnapshot($filters);

        return $this->pdf->download($doc['view'], $doc['data'], $doc['filename']);
    }

    public function respondAuditorActivityAdmin(Request $request): SymfonyResponse
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'auditor_id' => ['nullable', 'integer', 'exists:users,id'],
            'detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
        ]);

        $filters['auditor_scope'] = 'admin';

        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        $rows = $this->decision->adminAuditorRowCount(
            isset($filters['auditor_id']) ? (int) $filters['auditor_id'] : null,
        );

        if ($this->decision->shouldQueueAuditorActivity($rows, $detail, 'admin')) {
            return $this->queuedJson($request->user(), ExportType::AuditorActivity, $filters);
        }

        $doc = $this->documents->documentForAuditorActivity($filters);

        return $this->pdf->download($doc['view'], $doc['data'], $doc['filename']);
    }

    public function respondAuditorActivitySelf(Request $request): SymfonyResponse
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
        ]);

        $filters['auditor_scope'] = 'self';
        $filters['auditor_user_id'] = $request->user()->id;

        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        if ($this->decision->shouldQueueAuditorActivity(1, $detail, 'self')) {
            return $this->queuedJson($request->user(), ExportType::AuditorActivity, $filters);
        }

        $doc = $this->documents->documentForAuditorActivity($filters);

        return $this->pdf->download($doc['view'], $doc['data'], $doc['filename']);
    }

    /**
     * API: unified POST /api/exports/pdf
     *
     * @param  array<string, mixed>  $filters
     */
    public function respondApiExport(User $user, ExportType $type, array $filters): SymfonyResponse
    {
        return match ($type) {
            ExportType::ChecklistInstance => $this->apiChecklistInstance($user, $filters),
            ExportType::ChecklistReport => $this->apiChecklistReport($user, $filters),
            ExportType::ChecklistTemplate => $this->apiChecklistTemplate($user, $filters),
            ExportType::ComplianceSnapshot => $this->apiComplianceSnapshot($user, $filters),
            ExportType::AuditorActivity => $this->apiAuditorActivity($user, $filters),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function apiChecklistInstance(User $user, array $filters): SymfonyResponse
    {
        $instance = ChecklistInstance::query()->findOrFail((int) ($filters['checklist_instance_id'] ?? 0));

        Gate::authorize('exportPdf', $instance);

        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        if ($this->decision->shouldQueueChecklistInstance($instance, $detail)) {
            return $this->queuedJson($user, ExportType::ChecklistInstance, $filters);
        }

        $doc = $this->documents->documentForChecklistInstance($instance, $filters);

        return $this->pdf->download($doc['view'], $doc['data'], $doc['filename']);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function apiChecklistReport(User $user, array $filters): SymfonyResponse
    {
        abort_unless($user->hasRole('admin'), 403);

        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        $matched = $this->documents->checklistReportMatchedCount($filters);

        if ($this->decision->shouldQueueChecklistReport($matched, $detail)) {
            return $this->queuedJson($user, ExportType::ChecklistReport, $filters);
        }

        $doc = $this->documents->documentForChecklistReport($filters, self::SYNC_REPORT_ROW_CAP);

        return $this->pdf->download($doc['view'], $doc['data'], $doc['filename']);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function apiChecklistTemplate(User $user, array $filters): SymfonyResponse
    {
        $template = ChecklistTemplate::withTrashed()->findOrFail((int) ($filters['checklist_template_id'] ?? 0));

        Gate::authorize('exportPdf', $template);

        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        if ($this->decision->shouldQueueChecklistTemplate($template, $detail)) {
            return $this->queuedJson($user, ExportType::ChecklistTemplate, $filters);
        }

        $doc = $this->documents->documentForChecklistTemplate($template, $filters);

        return $this->pdf->download($doc['view'], $doc['data'], $doc['filename']);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function apiComplianceSnapshot(User $user, array $filters): SymfonyResponse
    {
        abort_unless($user->hasRole('admin'), 403);

        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        $instanceCount = $this->decision->snapshotFilteredInstanceCount(
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null,
        );

        $queueSnapshot = $this->decision->shouldQueueComplianceSnapshot($instanceCount, $detail);

        $forceSyncMax = config('pdf_exports.compliance_snapshot_force_sync_max_instances');
        if ($forceSyncMax !== null && (int) $forceSyncMax !== 0 && $instanceCount <= (int) $forceSyncMax) {
            $queueSnapshot = false;
        }

        if ($queueSnapshot) {
            return $this->queuedJson($user, ExportType::ComplianceSnapshot, $filters);
        }

        $doc = $this->documents->documentForComplianceSnapshot($filters);

        return $this->pdf->download($doc['view'], $doc['data'], $doc['filename']);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function apiAuditorActivity(User $user, array $filters): SymfonyResponse
    {
        if ($user->hasRole('auditor') && ! $user->hasRole('admin')) {
            $filters['auditor_scope'] = 'self';
            $filters['auditor_user_id'] = $user->id;
        }

        $scope = ($filters['auditor_scope'] ?? 'admin') === 'self' ? 'self' : 'admin';

        if ($scope === 'self') {
            $filters['auditor_scope'] = 'self';
            $filters['auditor_user_id'] = $filters['auditor_user_id'] ?? $user->id;

            $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

            if ($this->decision->shouldQueueAuditorActivity(1, $detail, 'self')) {
                return $this->queuedJson($user, ExportType::AuditorActivity, $filters);
            }

            $doc = $this->documents->documentForAuditorActivity($filters);

            return $this->pdf->download($doc['view'], $doc['data'], $doc['filename']);
        }

        abort_unless($user->hasRole('admin'), 403);

        $filters['auditor_scope'] = 'admin';

        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        $rows = $this->decision->adminAuditorRowCount(
            isset($filters['auditor_id']) ? (int) $filters['auditor_id'] : null,
        );

        if ($this->decision->shouldQueueAuditorActivity($rows, $detail, 'admin')) {
            return $this->queuedJson($user, ExportType::AuditorActivity, $filters);
        }

        $doc = $this->documents->documentForAuditorActivity($filters);

        return $this->pdf->download($doc['view'], $doc['data'], $doc['filename']);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function queuedJson(User $user, ExportType $type, array $filters): JsonResponse
    {
        $export = $this->enqueue($user, $type, $filters);
        $export->refresh();

        // Sync queue driver runs the job before this response is built — skip polling when already done.
        if ($export->status === ExportStatus::Completed && $export->hasStoredFile()) {
            if (config('pdf_exports.log_lifecycle', true)) {
                Log::info('pdf_export.queued_json.immediate_complete', [
                    'export_id' => $export->id,
                    'type' => $type->value,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Export ready.',
                'data' => [
                    'async' => false,
                    'export_uuid' => $export->uuid,
                    'status' => $export->status->value,
                    'download_url' => URL::temporarySignedRoute(
                        'exports.download',
                        now()->addHour(),
                        ['export' => $export->uuid],
                    ),
                    'status_url' => route('exports.status', $export),
                ],
            ]);
        }

        if (config('pdf_exports.log_lifecycle', true)) {
            Log::info('pdf_export.queued_json.poll_required', [
                'export_id' => $export->id,
                'type' => $type->value,
                'status' => $export->status->value,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your export is being prepared.',
            'data' => [
                'async' => true,
                'export_uuid' => $export->uuid,
                'status' => $export->status->value,
                'status_url' => route('exports.status', $export),
            ],
        ], 202);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function enqueue(User $user, ExportType $type, array $filters): Export
    {
        $hash = $this->dedupeHash($user, $type, $filters);
        $existing = $this->findRecentDuplicate($user, $hash);

        if ($existing !== null) {
            if (config('pdf_exports.log_lifecycle', true)) {
                Log::info('pdf_export.enqueue.deduped', [
                    'export_id' => $existing->id,
                    'type' => $type->value,
                    'status' => $existing->status->value,
                ]);
            }

            return $existing;
        }

        $export = Export::query()->create([
            'user_id' => $user->id,
            'export_type' => $type,
            'status' => ExportStatus::Queued,
            'filters' => $filters,
            'dedupe_hash' => $hash,
            'is_inline' => false,
            'disk' => 'exports',
            'original_filename' => null,
        ]);

        GenerateStoredPdfExportJob::dispatch($export->id);

        if (config('pdf_exports.log_lifecycle', true)) {
            Log::info('pdf_export.enqueue.dispatched', [
                'export_id' => $export->id,
                'type' => $type->value,
                'queue' => config('queue.default'),
            ]);
        }

        return $export;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function dedupeHash(User $user, ExportType $type, array $filters): string
    {
        $normalized = $filters;
        ksort($normalized);

        return hash('sha256', $user->id.'|'.$type->value.'|'.json_encode($normalized));
    }

    private function findRecentDuplicate(User $user, string $hash): ?Export
    {
        $ttl = (int) config('pdf_exports.dedupe_ttl_seconds', 600);

        return Export::query()
            ->where('user_id', $user->id)
            ->where('dedupe_hash', $hash)
            ->whereIn('status', [ExportStatus::Queued, ExportStatus::Processing])
            ->where('created_at', '>=', now()->subSeconds($ttl))
            ->first();
    }
}
