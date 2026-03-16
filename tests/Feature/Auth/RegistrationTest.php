<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_can_register_other_users(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'role' => 'ADMIN',
        ]);

        $response = $this->actingAs($user)->post('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'USER',
        ]);

        expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
        $response->assertNoContent();
    }

    public function test_manager_users_can_register_other_users(): void
    {
        $user = User::create([
            'name' => 'Manager User',
            'email' => 'manager@admin.com',
            'password' => bcrypt('password'),
            'role' => 'ADMIN',
        ]);

        $response = $this->actingAs($user)->post('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'USER',
        ]);

        expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
        $response->assertNoContent();
    }

    public function test_users_cant_register_other_users(): void
    {
        $user = User::create([
            'name' => 'Manager User',
            'email' => 'manager@admin.com',
            'password' => bcrypt('password'),
            'role' => 'USER',
        ]);

        $response = $this->actingAs($user)->post('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'USER',
        ]);

        expect(User::where('email', 'test@example.com')->exists())->toBeFalse();
        $response->assertStatus(403);
    }

    public function test_finance_cant_register_other_users(): void
    {
        $user = User::create([
            'name' => 'Manager User',
            'email' => 'manager@admin.com',
            'password' => bcrypt('password'),
            'role' => 'FINANCE',
        ]);

        $response = $this->actingAs($user)->post('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'USER',
        ]);

        expect(User::where('email', 'test@example.com')->exists())->toBeFalse();
        $response->assertStatus(403);
    }
}
