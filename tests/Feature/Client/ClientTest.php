<?php

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Client', function () {

    uses(RefreshDatabase::class);

    it('should fail to index Clients if user is not admin', function () {
        $user = User::factory()->create(['role' => 'USER']);
        Client::create(['name' => 'client_test1', 'email' => 'client@example.com']);

        $response = $this->actingAs($user)->get('/api/clients');
        $response->assertStatus(403);
    });

    it('should index Clients', function () {
        $user = User::factory()->create(['role' => 'ADMIN']);
        Client::create(['name' => 'client_test1', 'email' => 'client@example.com']);

        $response = $this->actingAs($user)->get('/api/clients');
        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(1);
    });

    it('should fail to show Client details if user is not admin', function () {
        $user = User::factory()->create(['role' => 'USER']);
        $client = Client::create(['name' => 'client_test1', 'email' => 'client@example.com']);

        $response = $this->actingAs($user)->get("/api/clients/{$client->id}");
        $response->assertStatus(403);
    });

    it('should show Client details', function () {
        $user = User::factory()->create(['role' => 'ADMIN']);
        $client = Client::create(['name' => 'client_test1', 'email' => 'client@example.com']);

        $response = $this->actingAs($user)->get("/api/clients/{$client->id}");
        $response->assertStatus(200);
        expect($response->json('data.name'))->toBe('client_test1');
    });

    it('should fail to create a Client without a name', function () {
        expect(fn() => Client::create(['email' => 'client@example.com']))->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('should fail to create a Client with an email already taken', function () {
        Client::create(['name' => 'client_test', 'email' => 'client@example.com']);
        expect(fn() => Client::create(['name' => 'client_test2', 'email' => 'client@example.com']))
            ->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('should fail to create Client unauthenticated', function () {
        $response = $this->withHeaders(['Accept' => 'application/json'])->post('/api/clients', ['name' => 'client_test', 'email' => 'client@example.com']);

        $response->assertStatus(401);
    });

    it('should fail to create Client as user or finance', function (string $role) {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/clients', ['name' => 'client_test', 'email' => 'client@example.com']);

        $response->assertStatus(403);
    })->with(['USER', 'FINANCE']);

    it('should create a Client sucessfully', function () {
        $client = Client::create(['name' => 'client_test', 'email' => 'client@example.com']);

        expect($client->exists())->toBeTrue();
    });

    it('should create a Client as admin or manager', function (string $role) {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/clients', ['name' => 'client_test', 'email' => 'client@example.com']);

        $response->assertStatus(201);
        expect($response->json('data.name'))->toBe('client_test');
        expect($response->json('data.email'))->toBe('client@example.com');
    })->with(['ADMIN', 'MANAGER']);

    it('should fail to update a Client without authentication', function () {
        Client::create(['name' => 'client_test', 'email' => 'test@exmaple.com']);

        $response = $this->withHeaders(['Accept' => 'application/json'])->put('/api/clients/1', ['name' => 'updated_client']);

        $response->assertStatus(401);
    });

    it('should fail to update a Client as user or finance', function (string $role) {
        $user = User::factory()->create(['role' => $role]);
        Client::create(['name' => 'client_test', 'email' => 'test@exmaple.com']);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->patch('/api/clients/1', ['name' => 'updated_client']);

        $response->assertStatus(403);
    })->with(['USER', 'FINANCE']);

    it('should update a Client successfully', function () {
        $client = Client::create(['name' => 'client_test', 'email' => 'test@exmaple.com']);

        $response = $this->actingAs(User::factory()->create(['role' => 'ADMIN']))
            ->withHeaders(['Accept' => 'application/json'])
            ->put("/api/clients/{$client->id}", ['name' => 'updated_client']);

        $response->assertStatus(200);
    });

    it('should fail to delete a Client without authentication', function () {
        $client = Client::create(['name' => 'client_test', 'email' => 'test@exmaple.com']);

        $response = $this->withHeaders(['Accept' => 'application/json'])->delete("/api/clients/{$client->id}");

        $response->assertStatus(401);
    });

    it('should fail to delete a Client as user or finance', function (string $role) {
        $user = User::factory()->create(['role' => $role]);
        $client = Client::create(['name' => 'client_test', 'email' => 'test@exmaple.com']);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->delete("/api/clients/{$client->id}");

        $response->assertStatus(403);
    })->with(['USER', 'FINANCE']);

    it('should delete a Client successfully', function (string $role) {
        $user = User::factory()->create(['role' => $role]);
        $client = Client::create(['name' => 'client_test', 'email' => 'test@exmaple.com']);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->delete("/api/clients/{$client->id}");

        $response->assertStatus(204);
    })->with(['ADMIN', 'MANAGER']);
});
