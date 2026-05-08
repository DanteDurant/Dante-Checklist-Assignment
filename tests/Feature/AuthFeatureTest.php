<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_and_user_payload(): void
    {
        Role::create(['name' => 'admin']);

        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password', // hashed via User cast
        ]);
        $user->assignRole('admin');

        $res = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
            'device_name' => 'phpunit',
        ]);

        $res->assertOk()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'roles', 'permissions'],
                ],
            ]);
    }

    public function test_logout_revokes_current_token(): void
    {
        Role::create(['name' => 'admin']);

        $user = User::factory()->create(['password' => 'password']);
        $user->assignRole('admin');

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'phpunit',
        ])->assertOk();

        $token = $login->json('data.token');
        $this->assertNotEmpty($token);
        $tokenId = (int) explode('|', $token, 2)[0];
        $this->assertGreaterThan(0, $tokenId);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }
}

