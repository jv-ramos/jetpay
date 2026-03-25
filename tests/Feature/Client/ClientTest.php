<?php

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Client', function () {

    uses(RefreshDatabase::class);

    it('should create a Client sucessfully', function () {
        $client = Client::create(['name' => 'client_test', 'email' => 'client@example.com']);

        expect($client->exists())->toBeTrue();
    });

    it('should fail to create a Client without a name', function () {
        expect(fn() => Client::create(['email' => 'client@example.com']))->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('should fail to create a Client with an email already taken', function () {
        Client::create(['name' => 'client_test', 'email' => 'client@example.com']);
        expect(fn() => Client::create(['name' => 'client_test2', 'email' => 'client@example.com']))
            ->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('should index Clients', function () {
        $user = User::factory()->create(['role' => 'ADMIN']);
        Client::create(['name' => 'client_test1', 'email' => 'client@example.com']);

        $response = $this->actingAs($user)->get('/api/clients');
        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(1);
    });

    it('should fail to index Clients if user is not admin', function () {
        $user = User::factory()->create(['role' => 'USER']);
        Client::create(['name' => 'client_test1', 'email' => 'client@example.com']);

        $response = $this->actingAs($user)->get('/api/clients');
        $response->assertStatus(403);
    });

    it('should show Client details', function () {
        $user = User::factory()->create(['role' => 'ADMIN']);
        $client = Client::create(['name' => 'client_test1', 'email' => 'client@example.com']);

        $response = $this->actingAs($user)->get("/api/clients/{$client->id}");
        $response->assertStatus(200);
        expect($response->json('data.name'))->toBe('client_test1');
    });

    it('should fail to show Client details if user is not admin', function () {
        $user = User::factory()->create(['role' => 'USER']);
        $client = Client::create(['name' => 'client_test1', 'email' => 'client@example.com']);

        $response = $this->actingAs($user)->get("/api/clients/{$client->id}");
        $response->assertStatus(403);
    });
});
