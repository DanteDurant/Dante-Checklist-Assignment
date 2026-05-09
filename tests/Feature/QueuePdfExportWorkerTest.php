<?php

namespace Tests\Feature;

use App\Enums\ExportStatus;
use App\Enums\ExportType;
use App\Jobs\GenerateStoredPdfExportJob;
use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Verifies jobs land on the database driver and a worker can process them end-to-end.
 * PHPUnit defaults QUEUE_CONNECTION=sync; this test overrides to database explicitly.
 */
final class QueuePdfExportWorkerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);

        Config::set('queue.default', 'database');
        Storage::fake('exports');
    }

    public function test_database_driver_queues_job_and_worker_completes_export(): void
    {
        $user = User::factory()->create()->assignRole('admin');

        $export = Export::query()->create([
            'user_id' => $user->id,
            'export_type' => ExportType::ComplianceSnapshot,
            'status' => ExportStatus::Queued,
            'filters' => [
                'detail' => 'summary',
                'date_from' => null,
                'date_to' => null,
            ],
            'is_inline' => false,
            'disk' => 'exports',
        ]);

        GenerateStoredPdfExportJob::dispatch($export->id);

        $this->assertGreaterThan(0, DB::table('jobs')->count(), 'Expected a row in the jobs table (database queue).');

        Artisan::call('queue:work', ['--once' => true]);

        $export->refresh();

        $this->assertSame(ExportStatus::Completed, $export->status);
        $this->assertNotNull($export->relative_path);
        $this->assertTrue(Storage::disk('exports')->exists($export->relative_path));

        $this->assertSame(0, DB::table('jobs')->count(), 'Job row should be removed after successful processing.');
    }
}
