<?php

use App\Services\Gateway\GatewayOneService;
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

        $service = new GatewayOneService();
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

        $service = new GatewayOneService();
        $result = $service->refund('fake-external-id');

        expect($result['id'])->toBe('fake-external-id');
        expect($result['status'])->toBe('charged_back');
    });
});
