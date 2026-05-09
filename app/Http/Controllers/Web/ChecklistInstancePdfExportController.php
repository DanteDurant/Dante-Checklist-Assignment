<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChecklistInstance;
use App\Services\Exports\ExportPdfCoordinator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ChecklistInstancePdfExportController extends Controller
{
    public function __invoke(
        Request $request,
        ChecklistInstance $instance,
        ExportPdfCoordinator $coordinator,
    ): SymfonyResponse {
        return $coordinator->respondChecklistInstance($request, $instance);
    }
}
