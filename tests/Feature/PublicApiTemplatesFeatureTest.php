<?php

namespace Tests\Feature;

use App\Enums\ChecklistTemplateStatus;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Admin template CRUD on stable `/api/templates/*`.
 */
final class PublicApiTemplatesFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_create_update_and_delete_templates(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        $this->getJson('/api/templates?per_page=10')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items',
                    'meta' => ['current_page', 'per_page', 'total', 'last_page'],
                ],
            ]);

        $create = $this->postJson('/api/templates', [
            'title' => 'Public API Template',
            'description' => 'Created in feature test',
            'status' => ChecklistTemplateStatus::Draft->value,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'Public API Template');

        $id = (int) $create->json('data.id');

        $this->putJson("/api/templates/{$id}", [
            'title' => 'Updated Title',
            'status' => ChecklistTemplateStatus::Published->value,
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated Title');

        $this->deleteJson("/api/templates/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('checklist_templates', ['id' => $id]);
    }

    public function test_auditor_cannot_create_templates_on_public_api(): void
    {
        $auditor = User::factory()->create();
        $auditor->assignRole('auditor');
        Sanctum::actingAs($auditor);

        $this->postJson('/api/templates', [
            'title' => 'Should fail',
            'status' => ChecklistTemplateStatus::Draft->value,
        ])->assertForbidden();
    }

    public function test_auditor_cannot_access_template_show_on_admin_only_route(): void
    {
        $auditor = User::factory()->create();
        $auditor->assignRole('auditor');
        Sanctum::actingAs($auditor);

        $template = ChecklistTemplate::factory()->published()->create();

        $this->getJson("/api/templates/{$template->id}")
            ->assertForbidden();
    }
}
