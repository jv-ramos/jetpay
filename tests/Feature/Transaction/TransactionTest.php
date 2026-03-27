<?php

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

describe('Transaction', function () {

    uses(RefreshDatabase::class);

    it('should be created successfully', function () {
        $client = Client::create([
            'name' => 'Client 1',
            'email' => 'client1@exmaple.com',
        ]);

        $product = Product::create([
            'name' => 'Product 1',
            'amount' => 1000,
        ]);

        Http::fake([
            'localhost:3001/login'        => Http::response(['token' => 'fake-token'], 200),
            'localhost:3001/transactions' => Http::sequence()
                ->push(['id' => 'fake-external-id'], 201)
                ->push(['data' => [
                    ['id' => 'fake-external-id', 'status' => 'paid']
                ]], 200),
        ]);

        Gateway::create(['is_active' => true, 'priority' => 1, 'name' => 'gateway_1']);

        $response = $this->postJson('/api/transactions', [
            'client_id'   => $client->id,
            'name'        => $client->name,
            'email'       => $client->email,
            'card_number' => '5569000000006063',
            'cvv'         => '010',
            'cart'        => [
                ['product_id' => $product->id, 'quantity' => 2]
            ],
        ]);
        $response->assertCreated()->assertJsonStructure(["data" => ['id', 'client_id', 'status', 'amount', 'products']]);
    });

    it('should index transactions', function () {
        $client = Client::create([
            'name' => 'Client 1',
            'email' => 'client1@exmaple.com',
        ]);

        $gateway = Gateway::create(['is_active' => true, 'priority' => 1, 'name' => 'gateway_1']);

        Transaction::create([
            'client_id' => $client->id,
            'gateway_id' => $gateway->id,
            'external_id' => 'abc123',
            'status' => 'pending',
            'amount' => 1000,
            'card_last_numbers' => '1234',
        ]);


        $this->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonStructure(["data" => [['id', 'client_id', 'gateway_id', 'external_id', 'status', 'amount', 'card_last_numbers', 'created_at']]]);
    });

    it('should show a transaction with its products', function () {
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
            'client_id' => $client->id,
            'gateway_id' => $gateway->id,
            'external_id' => 'abc123',
            'status' => 'pending',
            'amount' => 1000,
            'card_last_numbers' => '1234',
        ]);

        $transaction->products()->attach($product->id, ['quantity' => 2]);
        $transaction->load('products');

        $this->getJson("/api/transactions/{$transaction->id}")
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'client_id', 'gateway_id', 'external_id', 'status', 'amount', 'card_last_numbers', 'created_at', 'products']])
            ->assertJsonCount(1, 'data.products');
    });

    it('should refund a transaction as a user with FINANCE role', function () {
        \Illuminate\Support\Env::getRepository()->set('GATEWAY1_URL', 'http://localhost:3001');

        $user = User::factory()->create(['role' => 'FINANCE']);

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
            'client_id' => $client->id,
            'gateway_id' => $gateway->id,
            'external_id' => 'abc123',
            'status' => 'pending',
            'amount' => 1000,
            'card_last_numbers' => '1234',
        ]);

        Http::fake([
            'localhost:3001/login' => Http::response(['token' => 'fake-token'], 200),
            'localhost:3001/transactions/abc123/charge_back' => Http::response(['id' => 'abc123', 'status' => 'charged_back'], 200)
        ]);

        $transaction->products()->attach($product->id, ['quantity' => 2]);
        $transaction->load('products');

        $this->actingAs($user)->postJson("/api/transactions/{$transaction->id}/refund")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'client_id', 'gateway_id', 'external_id', 'status', 'amount', 'card_last_numbers', 'created_at']])
            ->assertJsonPath('data.status', 'charged_back');
    });

    it('should not refund a charged_back transaction', function () {
        \Illuminate\Support\Env::getRepository()->set('GATEWAY1_URL', 'http://localhost:3001');

        $user = User::factory()->create(['role' => 'FINANCE']);

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
            'client_id' => $client->id,
            'gateway_id' => $gateway->id,
            'external_id' => 'abc123',
            'status' => 'charged_back',
            'amount' => 1000,
            'card_last_numbers' => '1234',
        ]);

        Http::fake([
            'localhost:3001/login' => Http::response(['token' => 'fake-token'], 200),
            'localhost:3001/transactions/abc123/charge_back' => Http::response(['id' => 'abc123', 'status' => 'charged_back'], 200)
        ]);

        $transaction->products()->attach($product->id, ['quantity' => 2]);
        $transaction->load('products');

        $this->actingAs($user)->postJson("/api/transactions/{$transaction->id}/refund")
            ->assertStatus(422)->assertJson(['message' => 'Transaction already refunded.']);
    });

    it('should belong to a gateway', function () {
        $client = Client::create([
            'name' => 'Client 1',
            'email' => 'client1@exmaple.com',
        ]);

        $gateway = Gateway::create(['is_active' => true, 'priority' => 1, 'name' => 'gateway_1']);

        $transaction = Transaction::create([
            'client_id' => $client->id,
            'gateway_id' => $gateway->id,
            'external_id' => 'abc123',
            'status' => 'pending',
            'amount' => 1000,
            'card_last_numbers' => '1234',
        ]);

        expect($transaction->gateway)->toBeInstanceOf(Gateway::class);
        expect($transaction->gateway->id)->toBe($gateway->id);
    });
});
