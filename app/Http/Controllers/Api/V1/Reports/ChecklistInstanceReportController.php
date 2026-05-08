<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Application\Reporting\Queries\ChecklistInstanceReportQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Reports\ChecklistInstanceReportRequest;
use App\Http\Resources\Api\V1\Reports\ChecklistInstanceReportResource;
use Illuminate\Http\JsonResponse;

class ChecklistInstanceReportController extends Controller
{
    public function __construct(private readonly ChecklistInstanceReportQuery $query)
    {
    }

    public function index(ChecklistInstanceReportRequest $request): JsonResponse
    {
        $paginator = $this->query->paginate($request->validated());

        return response()->json([
            'data' => ChecklistInstanceReportResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}

