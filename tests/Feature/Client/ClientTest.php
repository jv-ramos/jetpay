<?php

use App\Models\Client;
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
        Client::create(['name' => 'client_test1', 'email' => 'client@example.com']);

        $response = $this->get('/api/clients');
        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(1);
    });
});
