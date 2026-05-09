<?php

namespace Tests\Feature;

use App\Enums\ExportStatus;
use App\Jobs\GenerateStoredPdfExportJob;
use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ensures the job's `failed()` lifecycle updates export rows for observability.
 */
final class GenerateStoredPdfExportJobFailureTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_callback_marks_export_as_failed_with_message(): void
    {
        $user = User::factory()->create()->assignRole('admin');

        $export = Export::factory()->create([
            'user_id' => $user->id,
            'status' => ExportStatus::Processing,
        ]);

        $job = new GenerateStoredPdfExportJob($export->id);
        $job->failed(new \RuntimeException('Simulated PDF failure'));

        $export->refresh();

        $this->assertSame(ExportStatus::Failed, $export->status);
        $this->assertStringContainsString('Simulated PDF failure', (string) $export->error_message);
        $this->assertNotNull($export->completed_at);
    }
}
