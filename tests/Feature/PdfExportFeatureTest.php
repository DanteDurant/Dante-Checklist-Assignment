<?php

namespace Tests\Feature;

use App\Enums\ChecklistTemplateStatus;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PdfExportFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'auditor']);
    }

    public function test_admin_can_download_completed_report_pdf(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.reports.checklist_instances_pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_auditor_cannot_hit_admin_report_pdf_route(): void
    {
        $auditor = User::factory()->create()->assignRole('auditor');

        $this->actingAs($auditor)
            ->get(route('admin.reports.checklist_instances_pdf'))
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
            ->get(route('auditor.instances.export_pdf', $instance))
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
            ->get(route('auditor.instances.export_pdf', $instance))
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
            ->get(route('auditor.instances.export_pdf', $instance))
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
            ->get(route('admin.instances.export_pdf', $instance))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_download_template_pdf_via_web(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        $template = ChecklistTemplate::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.templates.export_pdf', $template))
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
            ->get(route('admin.instances.export_pdf', $instance).'?detail=detailed')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
