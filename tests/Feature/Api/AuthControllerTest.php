<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_with_email_and_receive_a_bearer_token(): void
    {
        $user = User::factory()->create([
            'id_user' => 'S001',
            'email' => 'siswa@example.com',
            'role' => 'siswa',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'flutter-app',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.id_user', 'S001')
            ->assertJsonMissingPath('user.password');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => 'S001',
            'name' => 'flutter-app',
        ]);
    }

    public function test_invalid_password_returns_401_without_issuing_a_token(): void
    {
        User::factory()->create([
            'id_user' => 'S001',
            'email' => 'siswa@example.com',
            'role' => 'siswa',
        ]);

        $this->postJson('/api/login', [
            'email' => 'siswa@example.com',
            'password' => 'wrong-password',
            'device_name' => 'flutter-app',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_user_endpoint_returns_401_without_a_bearer_token(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_authenticated_user_can_retrieve_a_safe_profile(): void
    {
        User::factory()->create([
            'id_user' => 'S001',
            'email' => 'siswa@example.com',
            'role' => 'siswa',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'siswa@example.com',
            'password' => 'password',
            'device_name' => 'flutter-app',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('user.id_user', 'S001')
            ->assertJsonMissingPath('user.password');
    }

    public function test_user_can_revoke_the_current_bearer_token(): void
    {
        User::factory()->create([
            'id_user' => 'S001',
            'email' => 'siswa@example.com',
            'role' => 'siswa',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'siswa@example.com',
            'password' => 'password',
            'device_name' => 'flutter-app',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout')
            ->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        Auth::forgetGuards();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user')
            ->assertUnauthorized();
    }
}
