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

class ValidationFailuresFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'auditor']);
    }

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
            ->assertJsonValidationErrors(['question_text', 'answer_type', 'sort_order']);
    }
}

