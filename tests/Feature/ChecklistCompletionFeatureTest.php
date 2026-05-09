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

class ChecklistCompletionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_auditor_can_start_save_progress_and_complete_checklist(): void
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
        $q2 = ChecklistQuestion::factory()->create([
            'checklist_template_id' => $template->id,
            'type' => ChecklistQuestionType::Text,
            'is_required' => false,
            'is_active' => true,
        ]);

        $start = $this->postJson('/api/v1/auditor/checklist-instances', [
            'template_id' => $template->id,
        ])->assertOk();

        $instanceId = (int) $start->json('data.id');

        $this->putJson("/api/v1/auditor/checklist-instances/{$instanceId}/answers", [
            'answers' => [
                ['question_id' => $q1->id, 'value' => ['boolean' => true]],
                ['question_id' => $q2->id, 'value' => ['text' => 'hello']],
            ],
        ])->assertOk();

        $complete = $this->postJson("/api/v1/auditor/checklist-instances/{$instanceId}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', ChecklistInstanceStatus::Submitted->value);

        // After completion, saving progress should be blocked (policy + service guard).
        $this->putJson("/api/v1/auditor/checklist-instances/{$instanceId}/answers", [
            'answers' => [
                ['question_id' => $q1->id, 'value' => ['boolean' => false]],
            ],
        ])->assertForbidden();
    }

    public function test_required_questions_must_be_answered_before_completion(): void
    {
        $auditor = User::factory()->create();
        $auditor->assignRole('auditor');
        Sanctum::actingAs($auditor);

        $template = ChecklistTemplate::factory()->published()->create();
        $required = ChecklistQuestion::factory()->create([
            'checklist_template_id' => $template->id,
            'type' => ChecklistQuestionType::Boolean,
            'is_required' => true,
            'is_active' => true,
        ]);

        $start = $this->postJson('/api/v1/auditor/checklist-instances', [
            'template_id' => $template->id,
        ])->assertOk();

        $instanceId = (int) $start->json('data.id');

        $this->postJson("/api/v1/auditor/checklist-instances/{$instanceId}/complete")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['missing_required_question_ids']);

        $this->putJson("/api/v1/auditor/checklist-instances/{$instanceId}/answers", [
            'answers' => [
                ['question_id' => $required->id, 'value' => ['boolean' => true]],
            ],
        ])->assertOk();

        $this->postJson("/api/v1/auditor/checklist-instances/{$instanceId}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', ChecklistInstanceStatus::Submitted->value);
    }

    public function test_cannot_start_instance_from_non_published_template(): void
    {
        $auditor = User::factory()->create();
        $auditor->assignRole('auditor');
        Sanctum::actingAs($auditor);

        $template = ChecklistTemplate::factory()->create([
            'status' => ChecklistTemplateStatus::Draft,
        ]);

        $this->postJson('/api/v1/auditor/checklist-instances', [
            'template_id' => $template->id,
        ])->assertStatus(422);
    }
}
