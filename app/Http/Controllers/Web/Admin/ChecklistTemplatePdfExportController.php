<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistTemplate;
use App\Services\Exports\ExportPdfCoordinator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ChecklistTemplatePdfExportController extends Controller
{
    public function __invoke(
        Request $request,
        ChecklistTemplate $template,
        ExportPdfCoordinator $coordinator,
    ): SymfonyResponse {
        return $coordinator->respondChecklistTemplate($request, $template);
    }
}
