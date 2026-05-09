<?php

namespace App\Jobs;

use App\Application\Pdf\PdfExportDocumentFactory;
use App\Application\Pdf\PdfExportService;
use App\Enums\ExportStatus;
use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class GenerateStoredPdfExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [30, 120];

    public function __construct(public int $exportId) {}

    public function handle(PdfExportDocumentFactory $documents, PdfExportService $pdf): void
    {
        $export = Export::query()->find($this->exportId);

        if ($export === null) {
            return;
        }

        if ($export->status === ExportStatus::Completed && $export->hasStoredFile()) {
            return;
        }

        $export->forceFill([
            'status' => ExportStatus::Processing,
            'started_at' => $export->started_at ?? now(),
            'error_message' => null,
        ])->save();

        $doc = $documents->documentForExport($export);

        $relativePath = $export->uuid.'/'.uniqid('export_', true).'.pdf';

        $pdf->saveToDisk(
            $doc['view'],
            $doc['data'],
            $doc['filename'],
            $relativePath,
            $export->disk ?: 'exports',
        );

        $export->forceFill([
            'status' => ExportStatus::Completed,
            'relative_path' => $relativePath,
            'original_filename' => $doc['filename'],
            'completed_at' => now(),
            'error_message' => null,
        ])->save();
    }

    public function failed(?Throwable $exception): void
    {
        $export = Export::query()->find($this->exportId);

        if ($export === null) {
            return;
        }

        $export->forceFill([
            'status' => ExportStatus::Failed,
            'error_message' => $exception?->getMessage() ?? 'PDF generation failed.',
            'completed_at' => now(),
        ])->save();

        Log::warning('PDF export job failed', [
            'export_id' => $export->id,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
