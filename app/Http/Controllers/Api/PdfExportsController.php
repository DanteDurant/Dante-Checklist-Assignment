<?php

namespace App\Http\Controllers\Api;

use App\Application\Pdf\AuditorActivityAssembler;
use App\Application\Pdf\ChecklistInstanceExportAssembler;
use App\Application\Pdf\ComplianceSnapshotAssembler;
use App\Application\Pdf\ExportFilename;
use App\Application\Pdf\PdfExportService;
use App\Application\Reporting\Queries\ChecklistInstanceReportQuery;
use App\Enums\ExportDetailLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Reports\ChecklistInstanceReportRequest;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

final class PdfExportsController extends Controller
{
    private const EXPORT_LIMIT = 500;

    private const EXECUTIVE_PREVIEW_ROWS = 35;

    public function checklistInstance(
        Request $request,
        ChecklistInstance $checklist,
        PdfExportService $pdf,
        ChecklistInstanceExportAssembler $assembler,
    ): Response {
        $this->authorize('exportPdf', $checklist);

        $request->validate([
            'detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
            'sections' => ['nullable', 'string', 'max:500'],
        ]);

        $detail = ExportDetailLevel::fromQuery($request->query('detail'));
        $sections = ChecklistInstanceExportAssembler::sectionsFromQuery($request->query('sections'))
            ?? $assembler->defaultSections($detail);

        $payload = $assembler->assemble($checklist, $detail, $sections);

        $slug = strtolower(preg_replace('/[^\p{L}\p{N}\-_]+/u', '-', $checklist->template?->name ?? $checklist->public_id) ?? 'checklist');
        $slug = trim($slug, '-') ?: 'checklist';

        return $pdf->download(
            'pdf.checklist-instance',
            array_merge($payload, [
                'documentTitle' => $assembler->documentTitle($checklist, $detail),
            ]),
            ExportFilename::build($slug.'-'.$checklist->public_id, $detail)
        );
    }

    public function checklistReport(
        ChecklistInstanceReportRequest $request,
        ChecklistInstanceReportQuery $reportQuery,
        PdfExportService $pdf,
    ): Response {
        $filters = $request->validated();

        $detail = ExportDetailLevel::fromQuery($request->query('detail'));

        $results = $reportQuery->limitedCollection($filters, self::EXPORT_LIMIT);
        $totalMatched = $reportQuery->matchedCount($filters);

        $templateLabel = 'Any';
        if (! empty($filters['template_id'])) {
            $templateLabel = ChecklistTemplate::query()
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

        return $pdf->download(
            'pdf.completed-report',
            [
                'documentTitle' => $documentTitle,
                'detailLevel' => $detail,
                'results' => $results,
                'reportRows' => $reportRows,
                'previewRows' => $previewRows,
                'summaries' => $summaries,
                'truncated' => $totalMatched > $results->count(),
                'limit' => self::EXPORT_LIMIT,
                'totalMatched' => $totalMatched,
                'statusCounts' => $statusCounts,
                'executivePreviewCap' => self::EXECUTIVE_PREVIEW_ROWS,
            ],
            ExportFilename::build('completed-checklists-report', $detail)
        );
    }

    public function checklistTemplate(Request $request, ChecklistTemplate $template, PdfExportService $pdf): Response
    {
        $this->authorize('exportPdf', $template);

        $request->validate([
            'detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
        ]);

        $detail = ExportDetailLevel::fromQuery($request->query('detail'));

        $template->load([
            'questions' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
        ]);

        $documentTitle = match ($detail) {
            ExportDetailLevel::Summary => 'Template summary · '.$template->name,
            ExportDetailLevel::Executive => 'Template overview · '.$template->name,
            ExportDetailLevel::Detailed => 'Template specification · '.$template->name,
            default => 'Checklist template · '.$template->name,
        };

        return $pdf->download(
            'pdf.checklist-template',
            [
                'documentTitle' => $documentTitle,
                'detailLevel' => $detail,
                'template' => $template,
                'questions' => $template->questions,
            ],
            ExportFilename::build('template-'.$template->public_id, $detail)
        );
    }

    public function complianceSnapshot(
        Request $request,
        PdfExportService $pdf,
        ComplianceSnapshotAssembler $assembler,
    ): Response {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
        ]);

        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        $payload = $assembler->assemble([
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
        ], $detail->label());

        $documentTitle = match ($detail) {
            ExportDetailLevel::Executive => 'Compliance executive snapshot',
            ExportDetailLevel::Detailed => 'Compliance dashboard — detailed snapshot',
            default => 'Compliance dashboard snapshot',
        };

        return $pdf->download(
            'pdf.compliance-snapshot',
            array_merge($payload, [
                'documentTitle' => $documentTitle,
                'detailLevel' => $detail,
            ]),
            ExportFilename::build('compliance-snapshot', $detail)
        );
    }

    public function auditorActivityReport(
        Request $request,
        PdfExportService $pdf,
        AuditorActivityAssembler $assembler,
    ): Response {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'auditor_id' => ['nullable', 'integer', 'exists:users,id'],
            'detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
        ]);

        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        $payload = $assembler->assemble([
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'auditor_id' => $filters['auditor_id'] ?? null,
        ], $detail->label(), null);

        $documentTitle = match ($detail) {
            ExportDetailLevel::Executive => 'Auditor activity — executive summary',
            ExportDetailLevel::Detailed => 'Auditor activity — detailed workbook',
            default => 'Auditor activity report',
        };

        return $pdf->download(
            'pdf.auditor-activity',
            array_merge($payload, [
                'documentTitle' => $documentTitle,
                'detailLevel' => $detail,
            ]),
            ExportFilename::build('auditor-activity', $detail)
        );
    }
}
