<?php

namespace Tests\Feature;

use App\Enums\ChecklistQuestionType;
use App\Enums\ChecklistTemplateStatus;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ChecklistTemplateCrudFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'auditor']);
    }

    public function test_admin_can_create_update_show_and_delete_template(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        $create = $this->postJson('/api/v1/checklist-templates', [
            'title' => 'My Template',
            'description' => 'Desc',
            'status' => ChecklistTemplateStatus::Draft->value,
        ])->assertCreated();

        $templateId = (int) $create->json('data.id');
        $this->assertGreaterThan(0, $templateId);

        $this->getJson("/api/v1/checklist-templates/{$templateId}")
            ->assertOk()
            ->assertJsonPath('data.title', 'My Template');

        $this->patchJson("/api/v1/checklist-templates/{$templateId}", [
            'title' => 'Updated',
            'status' => ChecklistTemplateStatus::Published->value,
        ])->assertOk()
            ->assertJsonPath('data.title', 'Updated')
            ->assertJsonPath('data.status', ChecklistTemplateStatus::Published->value);

        $this->deleteJson("/api/v1/checklist-templates/{$templateId}")
            ->assertNoContent();
    }

    public function test_auditor_cannot_access_admin_template_crud_routes(): void
    {
        $auditor = User::factory()->create();
        $auditor->assignRole('auditor');
        Sanctum::actingAs($auditor);

        $this->getJson('/api/v1/checklist-templates')->assertForbidden();
    }

    public function test_admin_can_manage_nested_questions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        $template = ChecklistTemplate::factory()->create([
            'status' => ChecklistTemplateStatus::Draft,
        ]);

        $createQuestion = $this->postJson("/api/v1/checklist-templates/{$template->id}/questions", [
            'question_text' => 'Q1',
            'answer_type' => ChecklistQuestionType::Boolean->value,
            'required' => true,
            'sort_order' => 10,
        ])->assertCreated();

        $questionId = (int) $createQuestion->json('data.id');
        $this->assertGreaterThan(0, $questionId);

        $this->getJson("/api/v1/checklist-templates/{$template->id}/questions")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->patchJson("/api/v1/questions/{$questionId}", [
            'question_text' => 'Q1 updated',
        ])->assertOk()
            ->assertJsonPath('data.question_text', 'Q1 updated');

        $this->deleteJson("/api/v1/questions/{$questionId}")
            ->assertNoContent();
    }
}

