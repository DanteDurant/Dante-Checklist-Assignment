<?php

namespace Tests\Feature;

use App\Enums\ChecklistTemplateStatus;
use App\Jobs\GenerateStoredPdfExportJob;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PdfExportFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_completed_report_pdf(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.reports.checklist_instances_pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_auditor_cannot_hit_admin_report_pdf_route(): void
    {
        $auditor = User::factory()->create()->assignRole('auditor');

        $this->actingAs($auditor)
            ->post(route('admin.reports.checklist_instances_pdf'))
            ->assertForbidden();
    }

    public function test_auditor_can_download_own_completed_checklist_pdf_via_web(): void
    {
        $auditor = User::factory()->create()->assignRole('auditor');
        $template = ChecklistTemplate::factory()->published()->create(['status' => ChecklistTemplateStatus::Published]);

        $instance = ChecklistInstance::factory()->submitted()->create([
            'checklist_template_id' => $template->id,
            'auditor_id' => $auditor->id,
        ]);

        $this->actingAs($auditor)
            ->post(route('auditor.instances.export_pdf', $instance))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_auditor_cannot_download_other_auditor_completed_pdf_via_web(): void
    {
        $auditorA = User::factory()->create()->assignRole('auditor');
        $auditorB = User::factory()->create()->assignRole('auditor');
        $template = ChecklistTemplate::factory()->published()->create(['status' => ChecklistTemplateStatus::Published]);

        $instance = ChecklistInstance::factory()->submitted()->create([
            'checklist_template_id' => $template->id,
            'auditor_id' => $auditorA->id,
        ]);

        $this->actingAs($auditorB)
            ->post(route('auditor.instances.export_pdf', $instance))
            ->assertForbidden();
    }

    public function test_auditor_cannot_export_non_completed_instance(): void
    {
        $auditor = User::factory()->create()->assignRole('auditor');
        $template = ChecklistTemplate::factory()->published()->create(['status' => ChecklistTemplateStatus::Published]);

        $instance = ChecklistInstance::factory()->inProgress()->create([
            'checklist_template_id' => $template->id,
            'auditor_id' => $auditor->id,
        ]);

        $this->actingAs($auditor)
            ->post(route('auditor.instances.export_pdf', $instance))
            ->assertForbidden();
    }

    public function test_admin_can_download_checklist_instance_pdf(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        $auditor = User::factory()->create()->assignRole('auditor');
        $template = ChecklistTemplate::factory()->published()->create(['status' => ChecklistTemplateStatus::Published]);

        $instance = ChecklistInstance::factory()->submitted()->create([
            'checklist_template_id' => $template->id,
            'auditor_id' => $auditor->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.instances.export_pdf', $instance))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_download_template_pdf_via_web(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        $template = ChecklistTemplate::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.templates.export_pdf', $template))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_api_auditor_downloads_own_checklist_pdf_with_sanctum(): void
    {
        $auditor = User::factory()->create()->assignRole('auditor');
        $template = ChecklistTemplate::factory()->published()->create(['status' => ChecklistTemplateStatus::Published]);

        $instance = ChecklistInstance::factory()->submitted()->create([
            'checklist_template_id' => $template->id,
            'auditor_id' => $auditor->id,
        ]);

        Sanctum::actingAs($auditor);

        $this->get('/api/checklists/'.$instance->id.'/export-pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_api_admin_can_download_auditor_checklist_pdf(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        $auditor = User::factory()->create()->assignRole('auditor');
        $template = ChecklistTemplate::factory()->published()->create(['status' => ChecklistTemplateStatus::Published]);

        $instance = ChecklistInstance::factory()->submitted()->create([
            'checklist_template_id' => $template->id,
            'auditor_id' => $auditor->id,
        ]);

        Sanctum::actingAs($admin);

        $this->get('/api/checklists/'.$instance->id.'/export-pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_api_auditor_cannot_download_template_pdf(): void
    {
        $auditor = User::factory()->create()->assignRole('auditor');
        $template = ChecklistTemplate::factory()->create();

        Sanctum::actingAs($auditor);

        $this->get('/api/templates/'.$template->id.'/export-pdf')->assertForbidden();
    }

    public function test_api_admin_can_download_reports_pdf(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        Sanctum::actingAs($admin);

        $this->get('/api/reports/export-pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_api_auditor_cannot_download_reports_pdf(): void
    {
        $auditor = User::factory()->create()->assignRole('auditor');
        Sanctum::actingAs($auditor);

        $this->get('/api/reports/export-pdf')->assertForbidden();
    }

    public function test_api_admin_can_download_compliance_snapshot_pdf(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        Sanctum::actingAs($admin);

        $this->get('/api/reports/compliance-snapshot/export-pdf?detail=executive')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_api_auditor_cannot_download_compliance_snapshot_pdf(): void
    {
        $auditor = User::factory()->create()->assignRole('auditor');
        Sanctum::actingAs($auditor);

        $this->get('/api/reports/compliance-snapshot/export-pdf')->assertForbidden();
    }

    public function test_api_admin_can_download_auditor_activity_pdf(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        Sanctum::actingAs($admin);

        $this->get('/api/reports/auditor-activity/export-pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_checklist_export_accepts_detail_query_without_error(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        $auditor = User::factory()->create()->assignRole('auditor');
        $template = ChecklistTemplate::factory()->published()->create(['status' => ChecklistTemplateStatus::Published]);

        $instance = ChecklistInstance::factory()->submitted()->create([
            'checklist_template_id' => $template->id,
            'auditor_id' => $auditor->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.instances.export_pdf', $instance), ['detail' => 'detailed'])
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_api_report_pdf_queues_when_threshold_zero(): void
    {
        config(['pdf_exports.sync_max_report_rows' => -1]);

        Queue::fake();

        $admin = User::factory()->create()->assignRole('admin');
        Sanctum::actingAs($admin);

        $this->postJson('/api/exports/pdf', [
            'export_type' => 'checklist_report',
            'filters' => [
                'detail' => 'standard',
            ],
        ])
            ->assertStatus(202)
            ->assertJsonPath('data.async', true);

        Queue::assertPushed(GenerateStoredPdfExportJob::class);
    }

    public function test_admin_dashboard_portfolio_snapshot_returns_pdf_when_force_sync_override_applies(): void
    {
        config(['pdf_exports.sync_max_snapshot_instances' => -2]);
        config(['pdf_exports.compliance_snapshot_force_sync_max_instances' => 99_999]);

        $admin = User::factory()->create()->assignRole('admin');

        $res = $this->actingAs($admin)
            ->post(route('admin.dashboard.export_pdf'), ['detail' => 'standard']);

        $res->assertOk();
        $ct = strtolower((string) $res->headers->get('content-type'));
        $this->assertStringContainsString('application/pdf', $ct);
    }
}
