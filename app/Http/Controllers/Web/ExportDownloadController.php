<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Export;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportDownloadController extends Controller
{
    public function __invoke(Request $request, Export $export): StreamedResponse
    {
        $this->authorize('download', $export);

        abort_unless($export->hasStoredFile(), 404);

        $disk = Storage::disk($export->disk ?: 'exports');

        abort_unless($disk->exists($export->relative_path ?? ''), 404);

        $name = $export->original_filename ?: basename((string) $export->relative_path);

        return $disk->download($export->relative_path, $name);
    }
}
