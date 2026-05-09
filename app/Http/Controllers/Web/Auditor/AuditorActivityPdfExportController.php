<?php

namespace App\Http\Controllers\Web\Auditor;

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
            'detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
        ]);

        $detail = ExportDetailLevel::fromQuery($filters['detail'] ?? null);

        $payload = $assembler->assemble([
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
        ], $detail->label(), $request->user()->id);

        return $pdf->download(
            'pdf.auditor-activity',
            array_merge($payload, [
                'documentTitle' => 'My audit activity · '.$detail->label(),
                'detailLevel' => $detail,
            ]),
            ExportFilename::build('my-audit-activity', $detail)
        );
    }
}
