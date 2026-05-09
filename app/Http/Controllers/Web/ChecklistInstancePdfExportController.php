<?php

namespace App\Http\Controllers\Web;

use App\Application\Pdf\ChecklistInstanceExportAssembler;
use App\Application\Pdf\ExportFilename;
use App\Application\Pdf\PdfExportService;
use App\Enums\ExportDetailLevel;
use App\Http\Controllers\Controller;
use App\Models\ChecklistInstance;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

final class ChecklistInstancePdfExportController extends Controller
{
    public function __invoke(
        Request $request,
        ChecklistInstance $instance,
        PdfExportService $pdf,
        ChecklistInstanceExportAssembler $assembler,
    ): Response {
        $this->authorize('exportPdf', $instance);

        $request->validate([
            'detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
            'sections' => ['nullable', 'string', 'max:500'],
        ]);

        $detail = ExportDetailLevel::fromQuery($request->query('detail'));
        $sections = ChecklistInstanceExportAssembler::sectionsFromQuery($request->query('sections'))
            ?? $assembler->defaultSections($detail);

        $payload = $assembler->assemble($instance, $detail, $sections);

        $slug = strtolower(preg_replace('/[^\p{L}\p{N}\-_]+/u', '-', $instance->template?->name ?? $instance->public_id) ?? 'checklist');
        $slug = trim($slug, '-') ?: 'checklist';

        $filename = ExportFilename::build($slug.'-'.$instance->public_id, $detail);

        return $pdf->download(
            'pdf.checklist-instance',
            array_merge($payload, [
                'documentTitle' => $assembler->documentTitle($instance, $detail),
            ]),
            $filename
        );
    }
}
