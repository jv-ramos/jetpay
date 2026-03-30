<?php

use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Client Resource', function () {

    uses(RefreshDatabase::class);

    it('returns the correct structure', function () {
        $client = Client::create([
            'name' => 'Client 1',
            'email' => 'client1@exmaple.com',
        ]);

        $gateway = Gateway::create(['is_active' => true, 'priority' => 1, 'name' => 'gateway_1']);

        Product::create([
            'name' => 'Product 1',
            'amount' => 1000,
        ]);

        Transaction::create([
            'client_id'         => $client->id,
            'gateway_id'        => $gateway->id,
            'external_id'       => 'fake-external-id',
            'status'            => 'charged_back',
            'amount'            => 1000,
            'card_last_numbers' => '1234',
        ]);

        $clientResource = new ClientResource($client->load('transactions'));
        $data = $clientResource->toArray(request());

        expect($data)->toHaveKeys(['name', 'email', 'transactions']);
        expect($data['name'])->toBe($client->name);
        expect($data['email'])->toBe($client->email);
        expect($data['transactions'])->toBeInstanceOf(\Illuminate\Http\Resources\Json\AnonymousResourceCollection::class);
    });
});
