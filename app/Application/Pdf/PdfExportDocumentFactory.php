<?php

namespace App\Application\Pdf;

use App\Application\Reporting\Queries\ChecklistInstanceReportQuery;
use App\Enums\ExportDetailLevel;
use App\Enums\ExportType;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Models\Export;
use App\Models\User;

final class PdfExportDocumentFactory
{
    private const EXECUTIVE_PREVIEW_ROWS = 35;

    public function __construct(
        private readonly ChecklistInstanceExportAssembler $checklistAssembler,
        private readonly ChecklistInstanceReportQuery $reportQuery,
        private readonly ComplianceSnapshotAssembler $complianceAssembler,
        private readonly AuditorActivityAssembler $auditorAssembler,
    ) {}

    /**
     * Build DomPDF payload for a persisted export record (queued workers).
     *
     * @return array{view: string, data: array<string, mixed>, filename: string}
     */
    public function documentForExport(Export $export): array
    {
        $filters = $export->filters;
        $rowCap = (int) config('pdf_exports.queued_report_row_cap', 2000);

        return match ($export->export_type) {
            ExportType::ChecklistInstance => $this->documentForChecklistInstance(
                ChecklistInstance::query()->findOrFail((int) ($filters['checklist_instance_id'] ?? 0)),
                $filters,
            ),
            ExportType::ChecklistReport => $this->documentForChecklistReport($filters, $rowCap),
            ExportType::ChecklistTemplate => $this->documentForChecklistTemplate(
                ChecklistTemplate::withTrashed()->findOrFail((int) ($filters['checklist_template_id'] ?? 0)),
                $filters,
            ),
            ExportType::ComplianceSnapshot => $this->documentForComplianceSnapshot($filters),
            ExportType::AuditorActivity => $this->documentForAuditorActivity($filters),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{view: string, data: array<string, mixed>, filename: string}
     */
    public function documentForChecklistInstance(ChecklistInstance $checklist, array $filters): array
    {
        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);
        $sections = ChecklistInstanceExportAssembler::sectionsFromQuery($filters['sections'] ?? null)
            ?? $this->checklistAssembler->defaultSections($detail);

        $payload = $this->checklistAssembler->assemble($checklist, $detail, $sections);

        $slug = strtolower(preg_replace('/[^\p{L}\p{N}\-_]+/u', '-', $checklist->template?->name ?? $checklist->public_id) ?? 'checklist');
        $slug = trim($slug, '-') ?: 'checklist';

        return [
            'view' => 'pdf.checklist-instance',
            'data' => array_merge($payload, [
                'documentTitle' => $this->checklistAssembler->documentTitle($checklist, $detail),
            ]),
            'filename' => ExportFilename::build($slug.'-'.$checklist->public_id, $detail),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{view: string, data: array<string, mixed>, filename: string}
     */
    public function documentForChecklistReport(array $filters, int $rowCap): array
    {
        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        $results = $this->reportQuery->limitedCollection($filters, $rowCap);
        $totalMatched = $this->reportQuery->matchedCount($filters);

        $templateLabel = 'Any';
        if (! empty($filters['template_id'])) {
            $templateLabel = ChecklistTemplate::withTrashed()
                ->whereKey((int) $filters['template_id'])
                ->value('name')
                ?: 'Unknown template';
        }

        $auditorLabel = 'Any';
        if (! empty($filters['auditor_id'])) {
            /** @var User|null $aud */
            $aud = User::query()->find((int) $filters['auditor_id']);
            $auditorLabel = $aud
                ? $aud->name.' <'.$aud->email.'>'
                : 'Unknown auditor';
        }

        $summaries = [
            'date_from' => isset($filters['date_from']) && $filters['date_from'] !== null
                ? (string) $filters['date_from']
                : null,
            'date_to' => isset($filters['date_to']) && $filters['date_to'] !== null
                ? (string) $filters['date_to']
                : null,
            'template' => $templateLabel,
            'auditor' => $auditorLabel,
            'q' => $filters['q'] ?? null,
        ];

        $statusCounts = $results->countBy(fn ($row) => $row->status->value)->all();

        $previewRows = $detail === ExportDetailLevel::Executive
            ? $results->take(self::EXECUTIVE_PREVIEW_ROWS)
            : $results;

        $reportRows = $detail === ExportDetailLevel::Executive ? $previewRows : $results;

        $documentTitle = match ($detail) {
            ExportDetailLevel::Summary => 'Completed audits — summary export',
            ExportDetailLevel::Standard => 'Completed checklist report',
            ExportDetailLevel::Detailed => 'Completed checklist report — detailed register',
            ExportDetailLevel::Executive => 'Completed audits — executive overview',
        };

        return [
            'view' => 'pdf.completed-report',
            'data' => [
                'documentTitle' => $documentTitle,
                'detailLevel' => $detail,
                'results' => $results,
                'reportRows' => $reportRows,
                'previewRows' => $previewRows,
                'summaries' => $summaries,
                'truncated' => $totalMatched > $results->count(),
                'limit' => $rowCap,
                'totalMatched' => $totalMatched,
                'statusCounts' => $statusCounts,
                'executivePreviewCap' => self::EXECUTIVE_PREVIEW_ROWS,
            ],
            'filename' => ExportFilename::build('completed-checklists-report', $detail),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{view: string, data: array<string, mixed>, filename: string}
     */
    public function documentForChecklistTemplate(ChecklistTemplate $template, array $filters): array
    {
        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        $template->load([
            'questions' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
        ]);

        $documentTitle = match ($detail) {
            ExportDetailLevel::Summary => 'Template summary · '.$template->name,
            ExportDetailLevel::Executive => 'Template overview · '.$template->name,
            ExportDetailLevel::Detailed => 'Template specification · '.$template->name,
            default => 'Checklist template · '.$template->name,
        };

        return [
            'view' => 'pdf.checklist-template',
            'data' => [
                'documentTitle' => $documentTitle,
                'detailLevel' => $detail,
                'template' => $template,
                'questions' => $template->questions,
            ],
            'filename' => ExportFilename::build('template-'.$template->public_id, $detail),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{view: string, data: array<string, mixed>, filename: string}
     */
    public function documentForComplianceSnapshot(array $filters): array
    {
        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        $payload = $this->complianceAssembler->assemble([
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
        ], $detail->label());

        $documentTitle = match ($detail) {
            ExportDetailLevel::Executive => 'Compliance executive snapshot',
            ExportDetailLevel::Detailed => 'Compliance dashboard — detailed snapshot',
            default => 'Compliance dashboard snapshot',
        };

        return [
            'view' => 'pdf.compliance-snapshot',
            'data' => array_merge($payload, [
                'documentTitle' => $documentTitle,
                'detailLevel' => $detail,
            ]),
            'filename' => ExportFilename::build('compliance-snapshot', $detail),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{view: string, data: array<string, mixed>, filename: string}
     */
    public function documentForAuditorActivity(array $filters): array
    {
        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);
        $scope = $filters['auditor_scope'] ?? 'admin';

        if ($scope === 'self') {
            $userId = (int) ($filters['auditor_user_id'] ?? 0);
            $payload = $this->auditorAssembler->assemble([
                'date_from' => $filters['date_from'] ?? null,
                'date_to' => $filters['date_to'] ?? null,
            ], $detail->label(), $userId);

            return [
                'view' => 'pdf.auditor-activity',
                'data' => array_merge($payload, [
                    'documentTitle' => 'My audit activity · '.$detail->label(),
                    'detailLevel' => $detail,
                ]),
                'filename' => ExportFilename::build('my-audit-activity', $detail),
            ];
        }

        $payload = $this->auditorAssembler->assemble([
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'auditor_id' => isset($filters['auditor_id']) ? (int) $filters['auditor_id'] : null,
        ], $detail->label(), null);

        $documentTitle = match ($detail) {
            ExportDetailLevel::Executive => 'Auditor activity — executive summary',
            ExportDetailLevel::Detailed => 'Auditor activity — detailed workbook',
            default => 'Auditor activity report',
        };

        return [
            'view' => 'pdf.auditor-activity',
            'data' => array_merge($payload, [
                'documentTitle' => $documentTitle,
                'detailLevel' => $detail,
            ]),
            'filename' => ExportFilename::build('auditor-activity', $detail),
        ];
    }

    /**
     * Used by queue heuristics (report matched rows).
     *
     * @param  array<string, mixed>  $filters
     */
    public function checklistReportMatchedCount(array $filters): int
    {
        return $this->reportQuery->matchedCount($filters);
    }
}
