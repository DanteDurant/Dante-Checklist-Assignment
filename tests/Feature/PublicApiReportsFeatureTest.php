<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Admin reporting on stable `/api/reports`.
 */
final class PublicApiReportsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_fetch_filtered_reports_with_meta(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        Sanctum::actingAs($admin);

        $this->getJson('/api/reports?per_page=10')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items',
                    'meta' => ['current_page', 'per_page', 'total', 'last_page'],
                ],
            ]);
    }

    public function test_auditor_cannot_access_admin_reports_endpoint(): void
    {
        $auditor = User::factory()->create()->assignRole('auditor');
        Sanctum::actingAs($auditor);

        $this->getJson('/api/reports')->assertForbidden();
    }
}
