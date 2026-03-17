<?php

use App\Services\Gateway\GatewayTwoService;
use Illuminate\Support\Facades\Http;

describe('Gateway Two Service', function () {
    it('should create a transaction on gateway two successfully', function () {
        Http::fake([
            'localhost:3002/transacoes' => Http::sequence()
                ->push(['id' => 'fake-external-id'], 201)
                ->push([
                    'data' => [
                        ['id' => 'fake-external-id', 'status' => 'paid']
                    ]
                ], 200),
        ]);

        $service = new GatewayTwoService();
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

    it('should refund a transaction on gateway two', function () {
        Http::fake([
            'localhost:3002/transacoes/reembolso' => Http::response(['id' => 'fake-external-id'], 200),
            'localhost:3002/transacoes' => Http::response([
                'data' => [
                    ['id' => 'fake-external-id', 'status' => 'charged_back']
                ]
            ], 200),
        ]);

        $service = new GatewayTwoService();
        $result = $service->refund('fake-external-id');

        expect($result['id'])->toBe('fake-external-id');
        expect($result['status'])->toBe('charged_back');
    });
});
