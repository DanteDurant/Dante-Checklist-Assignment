<?php

namespace Tests\Feature;

use App\Enums\ChecklistQuestionType;
use App\Enums\ChecklistTemplateStatus;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ValidationFailuresFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_create_validation_fails(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/checklist-templates', [
            'title' => '',
            'status' => 'not-a-status',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'status']);
    }

    public function test_question_create_validation_fails(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        $template = ChecklistTemplate::factory()->create(['status' => ChecklistTemplateStatus::Draft]);

        $this->postJson("/api/v1/checklist-templates/{$template->id}/questions", [
            'question_text' => '',
            'answer_type' => 'nope',
            'sort_order' => -1,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['question_text', 'answer_type', 'sort_order'])
            ->assertJsonPath('message', 'Validation failed.');
    }

    public function test_duplicate_question_text_in_same_template_is_rejected(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        $template = ChecklistTemplate::factory()->create(['status' => ChecklistTemplateStatus::Draft]);

        $this->postJson("/api/v1/checklist-templates/{$template->id}/questions", [
            'question_text' => 'Risk Assessment Date',
            'answer_type' => ChecklistQuestionType::Boolean->value,
            'sort_order' => 1,
        ])->assertCreated();

        $this->postJson("/api/v1/checklist-templates/{$template->id}/questions", [
            'question_text' => '  risk assessment date  ',
            'answer_type' => ChecklistQuestionType::Text->value,
            'sort_order' => 2,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['question_text']);
    }

    public function test_question_update_cannot_duplicate_another_questions_label(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        $template = ChecklistTemplate::factory()->create(['status' => ChecklistTemplateStatus::Draft]);

        $first = $this->postJson("/api/v1/checklist-templates/{$template->id}/questions", [
            'question_text' => 'First Question',
            'answer_type' => ChecklistQuestionType::Boolean->value,
            'sort_order' => 1,
        ])->assertCreated()->json('data.id');

        $second = $this->postJson("/api/v1/checklist-templates/{$template->id}/questions", [
            'question_text' => 'Second Question',
            'answer_type' => ChecklistQuestionType::Boolean->value,
            'sort_order' => 2,
        ])->assertCreated()->json('data.id');

        $this->patchJson("/api/v1/questions/{$second}", [
            'question_text' => 'FIRST QUESTION',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['question_text']);
    }
}
