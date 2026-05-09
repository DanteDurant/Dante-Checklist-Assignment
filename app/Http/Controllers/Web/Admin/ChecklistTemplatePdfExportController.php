<?php

namespace App\Http\Controllers\Web\Admin;

use App\Application\Pdf\ExportFilename;
use App\Application\Pdf\PdfExportService;
use App\Enums\ExportDetailLevel;
use App\Http\Controllers\Controller;
use App\Models\ChecklistTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

final class ChecklistTemplatePdfExportController extends Controller
{
    public function __invoke(Request $request, ChecklistTemplate $template, PdfExportService $pdf): Response
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

        $slug = 'template-'.$template->public_id;

        return $pdf->download(
            'pdf.checklist-template',
            [
                'documentTitle' => $documentTitle,
                'detailLevel' => $detail,
                'template' => $template,
                'questions' => $template->questions,
            ],
            ExportFilename::build($slug, $detail)
        );
    }
}
