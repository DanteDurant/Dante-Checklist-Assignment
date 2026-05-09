<?php

namespace App\Http\Controllers\Web\Admin;

use App\Application\Pdf\ComplianceSnapshotAssembler;
use App\Application\Pdf\ExportFilename;
use App\Application\Pdf\PdfExportService;
use App\Enums\ExportDetailLevel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

final class ComplianceSnapshotPdfExportController extends Controller
{
    public function __invoke(
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
}
