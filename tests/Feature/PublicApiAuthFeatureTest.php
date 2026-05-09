<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Stable public API (`/api/*`) authentication — complements legacy `/api/v1/*` auth tests.
 */
final class PublicApiAuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_login_returns_token_and_user_payload(): void
    {
        $user = User::factory()->create([
            'email' => 'evaluator@example.com',
            'password' => 'password',
        ]);
        $user->assignRole('auditor');

        $this->postJson('/api/login', [
            'email' => 'evaluator@example.com',
            'password' => 'password',
            'device_name' => 'phpunit',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'roles'],
                ],
            ]);
    }

    public function test_public_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'exists@example.com',
            'password' => 'correct-password',
        ]);

        $this->postJson('/api/login', [
            'email' => 'exists@example.com',
            'password' => 'wrong-password',
            'device_name' => 'phpunit',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_public_me_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_public_me_returns_profile_when_authenticated(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);
        $user->assignRole('admin');

        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'me@example.com')
            ->assertJsonPath('data.roles.0', 'admin');
    }

    public function test_public_logout_revokes_tokens(): void
    {
        $user = User::factory()->create(['password' => 'password']);
        $user->assignRole('admin');

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'phpunit',
        ])->assertOk();

        $token = $login->json('data.token');
        $tokenId = (int) explode('|', $token, 2)[0];

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }
}
