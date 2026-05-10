<?php

namespace Tests\Feature;

use App\Enums\ExportStatus;
use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Unified JSON export API (`/api/exports/*`) — authorization and list/show contracts.
 */
final class ExportApiFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_own_exports(): void
    {
        $user = User::factory()->create();
        $user->assignRole('auditor');

        Export::factory()->count(2)->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->getJson('/api/exports')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.items')
            ->assertJsonStructure([
                'data' => [
                    'items',
                    'meta' => ['current_page', 'per_page', 'total', 'last_page'],
                ],
            ]);
    }

    public function test_auditor_cannot_view_another_users_export(): void
    {
        $owner = User::factory()->create()->assignRole('auditor');
        $other = User::factory()->create()->assignRole('auditor');

        $export = Export::factory()->create([
            'user_id' => $owner->id,
            'status' => ExportStatus::Queued,
        ]);

        Sanctum::actingAs($other);

        $this->getJson("/api/exports/{$export->uuid}")
            ->assertForbidden();
    }

    public function test_admin_can_view_any_export_when_listing_with_show_route(): void
    {
        $auditor = User::factory()->create()->assignRole('auditor');
        $admin = User::factory()->create()->assignRole('admin');

        $export = Export::factory()->create([
            'user_id' => $auditor->id,
            'status' => ExportStatus::Queued,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson("/api/exports/{$export->uuid}")
            ->assertOk()
            ->assertJsonPath('data.uuid', $export->uuid)
            ->assertJsonPath('data.status', 'queued');
    }

    public function test_post_exports_pdf_validates_export_type(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        Sanctum::actingAs($admin);

        $this->postJson('/api/exports/pdf', [
            'export_type' => 'not-a-real-type',
            'filters' => [],
        ])->assertStatus(422);
    }
}
