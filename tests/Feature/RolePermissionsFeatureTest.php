<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'auditor']);
    }

    public function test_admin_can_access_admin_routes_and_not_auditor_routes(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/ping')->assertOk();
        $this->getJson('/api/v1/auditor/ping')->assertForbidden();
    }

    public function test_auditor_can_access_auditor_routes_and_not_admin_routes(): void
    {
        $auditor = User::factory()->create();
        $auditor->assignRole('auditor');

        Sanctum::actingAs($auditor);

        $this->getJson('/api/v1/auditor/ping')->assertOk();
        $this->getJson('/api/v1/admin/ping')->assertForbidden();
    }
}

