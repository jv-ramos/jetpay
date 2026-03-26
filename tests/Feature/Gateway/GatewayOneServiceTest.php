<?php

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\Gateway\GatewayOneService;
use App\Services\Gateway\GatewayRequestService;
use Illuminate\Support\Facades\Http;

describe('Gateway One Service', function () {
    it('should create a transaction on gateway one successfully', function () {

        Http::fake([
            'localhost:3001/login'        => Http::response(['token' => 'fake-token'], 200),
            'localhost:3001/transactions' => Http::sequence()
                ->push(['id' => 'fake-external-id'], 201)
                ->push([
                    'data' => [
                        ['id' => 'fake-external-id', 'status' => 'paid']
                    ]
                ], 200),
        ]);

        $service = new GatewayOneService(new GatewayRequestService('http://localhost:3001'));
        $result = $service->createTransaction([
            'amount'      => 1000,
            'name'        => 'John Doe',
            'email'       => 'johndoe@example.com',
            'card_number' => '5569000000006063',
            'cvv'         => '010',
        ]);

        expect($result['id'])->toBe('fake-external-id');
        expect($result['status'])->toBe('paid');
    });

    it('should refund a transaction on gateway one', function () {
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
            'status'            => 'charged_back',
            'amount'            => 1000,
            'card_last_numbers' => '1234',
        ]);

        Http::fake([
            'localhost:3001/login' => Http::response(['token' => 'fake-token'], 200),
            'localhost:3001/transactions/fake-external-id/charge_back' => Http::response([
                "id" => "fake-external-id",
                "name" => "client_one",
                "email" => "client1@example.com",
                "status" => "charged_back",
                "card_first_digits" => "5569",
                "card_last_digits" => "6063",
                "amount" => 1000
            ], 200),
        ]);

        $service = new GatewayOneService(new GatewayRequestService('http://localhost:3001'));
        $result = $service->refund($transaction);

        expect($result['id'])->toBe('fake-external-id');
        expect($result['status'])->toBe('charged_back');
    });
});
