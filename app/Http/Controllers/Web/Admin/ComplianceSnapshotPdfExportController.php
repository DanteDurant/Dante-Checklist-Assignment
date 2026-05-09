<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\Exports\ExportPdfCoordinator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ComplianceSnapshotPdfExportController extends Controller
{
    public function __invoke(
        Request $request,
        ExportPdfCoordinator $coordinator,
    ): SymfonyResponse {
        return $coordinator->respondComplianceSnapshot($request);
    }
}
