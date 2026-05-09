<?php

namespace App\Http\Controllers\Web\Admin;

use App\Application\Pdf\AuditorActivityAssembler;
use App\Application\Pdf\ExportFilename;
use App\Application\Pdf\PdfExportService;
use App\Enums\ExportDetailLevel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

final class AuditorActivityPdfExportController extends Controller
{
    public function __invoke(
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
