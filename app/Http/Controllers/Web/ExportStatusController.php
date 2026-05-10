<?php

namespace App\Http\Controllers\Web;

use App\Enums\ExportStatus;
use App\Http\Controllers\Controller;
use App\Models\Export;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

final class ExportStatusController extends Controller
{
    public function __invoke(Request $request, Export $export): JsonResponse
    {
        $this->authorize('view', $export);

        $data = [
            'uuid' => $export->uuid,
            'status' => $export->status->value,
            'export_type' => $export->export_type->value,
            'created_at' => $export->created_at?->toIso8601String(),
            'started_at' => $export->started_at?->toIso8601String(),
            'completed_at' => $export->completed_at?->toIso8601String(),
            'filename' => $export->original_filename,
            'download_url' => null,
            'error' => null,
        ];

        if ($export->status === ExportStatus::Completed && $export->hasStoredFile()) {
            $data['download_url'] = URL::temporarySignedRoute(
                'exports.download',
                now()->addHour(),
                ['export' => $export->uuid],
                false,
            );
        }

        if ($export->status === ExportStatus::Failed) {
            $data['error'] = $export->error_message;
        }

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $data,
        ]);
    }
}
