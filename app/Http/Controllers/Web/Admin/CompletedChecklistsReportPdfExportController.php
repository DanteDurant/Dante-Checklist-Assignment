<?php

namespace App\Http\Controllers\Web\Admin;

use App\Application\Pdf\ExportFilename;
use App\Application\Pdf\PdfExportService;
use App\Application\Reporting\Queries\ChecklistInstanceReportQuery;
use App\Enums\ExportDetailLevel;
use App\Http\Controllers\Controller;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

final class CompletedChecklistsReportPdfExportController extends Controller
{
    private const EXPORT_LIMIT = 500;

    private const EXECUTIVE_PREVIEW_ROWS = 35;

    public function __invoke(
        Request $request,
        ChecklistInstanceReportQuery $reportQuery,
        PdfExportService $pdf,
    ): Response {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'template_id' => ['nullable', 'integer', 'exists:checklist_templates,id'],
            'auditor_id' => ['nullable', 'integer', 'exists:users,id'],
            'q' => ['nullable', 'string', 'max:255'],
            'detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
        ]);

        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

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
}
