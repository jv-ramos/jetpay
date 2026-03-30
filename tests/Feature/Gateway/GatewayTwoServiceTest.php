<?php

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\Gateway\GatewayRequestService;
use App\Services\Gateway\GatewayTwoService;
use Illuminate\Support\Facades\Http;

describe('Gateway Two Service', function () {
    it('should create a transaction on gateway two successfully', function () {
        afterEach(function () {
            Http::clearResolvedInstances();
        });
        Http::fake([
            'localhost:3002/transacoes' => Http::sequence()
                ->push(['id' => 'fake-external-id'], 201)
                ->push([
                    'data' => [
                        ['id' => 'fake-external-id', 'status' => 'paid']
                    ]
                ], 200),
        ]);

        $service = new GatewayTwoService(new GatewayRequestService('http://localhost:3002'));
        $result = $service->createTransaction([
            'client_id'   => 1,
            'amount'      => 1000,
            'name'        => 'John Doe',
            'email'       => 'johndoe@example.com',
            'card_number' => '5569000000006063',
            'cvv'         => '010',
        ]);

        expect($result['id'])->toBe('fake-external-id');
        expect($result['status'])->toBe('paid');
    });

    it('should refund a transaction on gateway two', function () {
        $client = Client::create([
            'name' => 'Client 1',
            'email' => 'client1@exmaple.com',
        ]);

        $gateway = Gateway::create(['is_active' => true, 'priority' => 1, 'name' => 'gateway_1']);

        Product::create([
            'name' => 'Product 1',
            'amount' => 1000,
        ]);

        $transaction = Transaction::create([
            'client_id'         => $client->id,
            'gateway_id'        => $gateway->id,
            'external_id'       => 'fake-external-id',
            'status'            => 'pending',
            'amount'            => 1000,
            'card_last_numbers' => '1234',
        ]);

        Http::fake([
            'localhost:3002/transacoes/reembolso' => Http::response(['id' => 'fake-external-id'], 200),
            'localhost:3002/transacoes' => Http::response([
                'data' => [
                    ['id' => 'fake-external-id', 'status' => 'charged_back']
                ]
            ], 200),
        ]);

        $service = new GatewayTwoService(new GatewayRequestService('http://localhost:3002'));
        $result = $service->refund($transaction);

        expect($result['id'])->toBe('fake-external-id');
        expect($result['status'])->toBe('charged_back');
    });
});
