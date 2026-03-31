<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('User', function () {
    uses(RefreshDatabase::class);

    it('should fail to view users without authentication', function () {
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->get('/api/user');

        $response->assertUnauthorized();
    });

    it('should fail to view users as a common user', function (string $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->get('/api/users')
            ->assertForbidden();
    })->with(['USER', 'FINANCE']);

    it('should view users successfully as an admin or manager', function (string $role) {
        $admin = User::factory()->create(['role' => $role]);

        $response = $this->actingAs($admin)
            ->withHeaders(['Accept' => 'application/json'])
            ->get('/api/users');

        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data')[0])->toHaveKeys(['id', 'name', 'email', 'role']);
    })->with(['ADMIN', 'MANAGER']);

    it('should fail to create a user without authentication', function () {
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post('/api/register', [
                'name' => 'New User',
                'email' => 'newuser@exmaple.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertUnauthorized();
    });

    it('should fail to create a user as a common user or finance', function (string $role) {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/register', [
                'name' => 'New User',
                'email' => 'new@email.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'USER',
            ]);

        $response->assertForbidden();
    })->with(['USER', 'FINANCE']);

    it('should fail to update a user without authentication', function () {
        $user = User::factory()->create();

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->put("/api/user/{$user->id}", [
                'name' => 'Updated Name',
                'email' => 'new@email.com',
            ]);

        $response->assertUnauthorized();
    });

    it("should't be able to update a user if the user isn't an admin or manager", function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->put("/api/user/{$otherUser->id}", [
                'name' => 'Updated Name',
                'email' => 'new@email.com',
            ]);

        $response->assertForbidden();
    });

    it('should update a user successfully with put method', function () {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->withHeaders(['Accept' => 'application/json'])
            ->put("/api/user/{$user->id}", [
                'name' => 'Updated Name',
                'email' => 'new@email.com',
            ]);

        $response->assertStatus(200);
    });

    it('should update a user successfully with patch method', function () {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->withHeaders(['Accept' => 'application/json'])
            ->patch("/api/user/{$user->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200);
    });

    it('should fail to delete a user without authentication', function () {
        $user = User::factory()->create();

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->delete("/api/user/{$user->id}");

        $response->assertUnauthorized();
    });

    it('should fail to delete a user as a common user', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->delete("/api/user/{$otherUser->id}");

        $response->assertForbidden();
    });

    it('should fail to delete a user as a finance user', function () {
        $finance = User::factory()->create(['role' => 'FINANCE']);
        $user = User::factory()->create();

        $response = $this->actingAs($finance)
            ->withHeaders(['Accept' => 'application/json'])
            ->delete("/api/user/{$user->id}");

        $response->assertForbidden();
    });

    it('should delete a user successfully as a manager', function () {
        $manager = User::factory()->create(['role' => 'MANAGER']);
        $user = User::factory()->create();

        $response = $this->actingAs($manager)
            ->withHeaders(['Accept' => 'application/json'])
            ->delete("/api/user/{$user->id}");

        $response->assertStatus(204);
    });

    it('should delete a user successfully as admin', function () {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->withHeaders(['Accept' => 'application/json'])
            ->delete("/api/user/{$user->id}");

        $response->assertStatus(204);
    });
});
