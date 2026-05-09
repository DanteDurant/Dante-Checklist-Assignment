<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Services\Exports\ExportPdfCoordinator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class PdfExportsController extends Controller
{
    public function checklistInstance(
        Request $request,
        ChecklistInstance $checklist,
        ExportPdfCoordinator $coordinator,
    ): SymfonyResponse {
        return $coordinator->respondChecklistInstance($request, $checklist);
    }

    public function checklistReport(
        Request $request,
        ExportPdfCoordinator $coordinator,
    ): SymfonyResponse {
        abort_unless($request->user()?->hasRole('admin'), 403);

        return $coordinator->respondCompletedChecklistsReport($request);
    }

    public function checklistTemplate(
        Request $request,
        ChecklistTemplate $template,
        ExportPdfCoordinator $coordinator,
    ): SymfonyResponse {
        return $coordinator->respondChecklistTemplate($request, $template);
    }

    public function complianceSnapshot(
        Request $request,
        ExportPdfCoordinator $coordinator,
    ): SymfonyResponse {
        abort_unless($request->user()?->hasRole('admin'), 403);

        return $coordinator->respondComplianceSnapshot($request);
    }

    public function auditorActivityReport(
        Request $request,
        ExportPdfCoordinator $coordinator,
    ): SymfonyResponse {
        abort_unless($request->user()?->hasRole('admin'), 403);

        return $coordinator->respondAuditorActivityAdmin($request);
    }
}
