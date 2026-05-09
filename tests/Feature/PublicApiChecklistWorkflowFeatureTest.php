<?php

namespace Tests\Feature;

use App\Enums\ChecklistInstanceStatus;
use App\Enums\ChecklistQuestionType;
use App\Enums\ChecklistTemplateStatus;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * End-to-end checklist flow on the stable `/api/checklists/*` surface.
 */
final class PublicApiChecklistWorkflowFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_auditor_can_start_save_draft_and_complete_via_public_api(): void
    {
        $auditor = User::factory()->create();
        $auditor->assignRole('auditor');
        Sanctum::actingAs($auditor);

        $template = ChecklistTemplate::factory()->published()->create();
        $q1 = ChecklistQuestion::factory()->create([
            'checklist_template_id' => $template->id,
            'type' => ChecklistQuestionType::Boolean,
            'is_required' => true,
            'is_active' => true,
        ]);

        $start = $this->postJson("/api/checklists/start/{$template->id}")
            ->assertCreated()
            ->assertJsonPath('success', true);

        $checklistId = (int) $start->json('data.id');

        $this->putJson("/api/checklists/{$checklistId}/save-draft", [
            'answers' => [
                (string) $q1->id => true,
            ],
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Draft saved');

        $this->putJson("/api/checklists/{$checklistId}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', ChecklistInstanceStatus::Submitted->value);

        $this->putJson("/api/checklists/{$checklistId}/save-draft", [
            'answers' => [(string) $q1->id => false],
        ])->assertForbidden();
    }

    public function test_public_api_complete_enforces_required_answers(): void
    {
        $auditor = User::factory()->create();
        $auditor->assignRole('auditor');
        Sanctum::actingAs($auditor);

        $template = ChecklistTemplate::factory()->published()->create();
        ChecklistQuestion::factory()->create([
            'checklist_template_id' => $template->id,
            'type' => ChecklistQuestionType::Boolean,
            'is_required' => true,
            'is_active' => true,
        ]);

        $start = $this->postJson("/api/checklists/start/{$template->id}")->assertCreated();
        $checklistId = (int) $start->json('data.id');

        $this->putJson("/api/checklists/{$checklistId}/complete")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['missing_required_question_ids']);
    }

    public function test_admin_cannot_start_checklist_via_auditor_route(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        $template = ChecklistTemplate::factory()->published()->create();

        $this->postJson("/api/checklists/start/{$template->id}")
            ->assertForbidden();
    }

    public function test_auditor_cannot_start_checklist_from_draft_template(): void
    {
        $auditor = User::factory()->create();
        $auditor->assignRole('auditor');
        Sanctum::actingAs($auditor);

        $template = ChecklistTemplate::factory()->create([
            'status' => ChecklistTemplateStatus::Draft,
        ]);

        $this->postJson("/api/checklists/start/{$template->id}")
            ->assertStatus(422);
    }
}
