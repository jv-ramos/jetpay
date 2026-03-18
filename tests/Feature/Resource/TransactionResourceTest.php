<?php

use App\Http\Resources\TransactionResource;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('TransactionResource', function () {

    uses(RefreshDatabase::class);

    it('should return the correct structure', function () {
        $client = \App\Models\Client::create([
            'name' => 'Client 1',
            'email' => 'client@example.com'
        ]);

        $gateway = \App\Models\Gateway::create([
            'name' => 'Gateway 1',
            'is_active' => true,
            'priority' => 1,
        ]);

        $product = Product::create([
            'name' => 'Product 1',
            'amount' => 500,
        ]);

        $transaction = Transaction::create([
            'client_id' => $client->id,
            'gateway_id' => $gateway->id,
            'external_id' => 'abc123',
            'status' => 'pending',
            'amount' => 1000,
            'card_last_numbers' => '1234',

        ]);

        $transaction->products()->attach([
            $product->id => ['quantity' => 2]
        ]);

        $transaction->load('products');

        $resource = new TransactionResource($transaction);
        $data = $resource->toArray(request());
        $products = $data['products']->toArray(request());

        expect($data['id'])->toBe($transaction->id);
        expect($data['client_id'])->toBe($transaction->client_id);
        expect($data['gateway_id'])->toBe($transaction->gateway_id);
        expect($data['external_id'])->toBe($transaction->external_id);
        expect($data['status'])->toBe($transaction->status);
        expect($data['amount'])->toBe($transaction->amount);
        expect($data['card_last_numbers'])->toBe($transaction->card_last_numbers);

        expect($products)->toHaveCount(1);
        expect($products[0])->toHaveKeys(['id', 'name', 'amount', 'quantity']);
        expect($data['products'][0]['id'])->toBe($product->id);
        expect($data['products'][0]['name'])->toBe($product->name);
        expect($data['products'][0]['amount'])->toBe($product->amount);
    });
});
