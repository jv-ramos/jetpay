<?php

use App\Http\Resources\ClientTransactionResource;
use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Client Transaction Resource', function () {

    uses(RefreshDatabase::class);

    it('returns the correct structure', function () {
        $client = Client::create([
            'name' => 'Client 1',
            'email' => 'client1@exmaple.com',
        ]);

        $gateway = Gateway::create(['is_active' => true, 'priority' => 1, 'name' => 'gateway_1']);

        $product = Product::create([
            'name' => 'Product 1',
            'amount' => 1000,
        ]);

        $transaction = Transaction::create([
            'client_id'         => $client->id,
            'gateway_id'        => $gateway->id,
            'external_id'       => 'fake-external-id',
            'status'            => 'paid',
            'amount'            => 1000,
            'card_last_numbers' => '1234',
        ]);

        $transaction->products()->attach($product->id, ['quantity' => 1]);
        $transaction->load('products');
        $clientTransactionResource = new ClientTransactionResource($transaction);
        $data = $clientTransactionResource->toArray(request());
        // $data = $clientTransactionResource->resolve();

        expect($data)->toHaveKeys(['id', 'client_id', 'external_id', 'status', 'amount', 'card_last_numbers', 'created_at', 'products']);
        expect($data['id'])->toBe($transaction->id);
        expect($data['client_id'])->toBe($client->id);
        expect($data['external_id'])->toBe('fake-external-id');
        expect($data['status'])->toBe('paid');
        expect($data['amount'])->toBe(1000);
        expect($data['card_last_numbers'])->toBe('1234');
        expect($data['products'])->toHaveCount(1);
    });
});
