<?php

namespace Tests\Feature;

use App\Enums\ChecklistInstanceStatus;
use App\Enums\ChecklistTemplateStatus;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportingFiltersFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporting_filters_only_return_completed_instances(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        Sanctum::actingAs($admin);

        $auditor = User::factory()->create()->assignRole('auditor');

        $templateA = ChecklistTemplate::factory()->published()->create(['status' => ChecklistTemplateStatus::Published]);
        $templateB = ChecklistTemplate::factory()->published()->create(['status' => ChecklistTemplateStatus::Published]);

        // Completed (submitted)
        $i1 = ChecklistInstance::factory()->create([
            'checklist_template_id' => $templateA->id,
            'auditor_id' => $auditor->id,
            'status' => ChecklistInstanceStatus::Submitted,
            'submitted_at' => Carbon::parse('2026-05-05 10:00:00'),
        ]);

        // Not completed
        ChecklistInstance::factory()->create([
            'checklist_template_id' => $templateA->id,
            'auditor_id' => $auditor->id,
            'status' => ChecklistInstanceStatus::InProgress,
            'submitted_at' => null,
        ]);

        // Completed, different template/auditor/date
        $otherAuditor = User::factory()->create()->assignRole('auditor');
        ChecklistInstance::factory()->create([
            'checklist_template_id' => $templateB->id,
            'auditor_id' => $otherAuditor->id,
            'status' => ChecklistInstanceStatus::Submitted,
            'submitted_at' => Carbon::parse('2026-04-01 10:00:00'),
        ]);

        $res = $this->getJson('/api/v1/admin/reports/checklist-instances?template_id='.$templateA->id.'&auditor_id='.$auditor->id.'&date_from=2026-05-01&date_to=2026-05-31');
        $res->assertOk();

        $ids = collect($res->json('data'))->pluck('id')->all();
        $this->assertEquals([$i1->id], $ids);
    }
}
