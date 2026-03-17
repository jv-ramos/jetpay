<?php

use App\Models\Gateway;
use App\Services\Gateway\GatewayFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Gateway Factory', function () {

    uses(RefreshDatabase::class);

    it('return a service sucessfully', function () {
        $gateway = Gateway::create(['name' => 'gateway_1', 'is_active' => true, 'priority' => 1]);
        $response = GatewayFactory::make($gateway);

        expect($response)->toBeInstanceOf(\App\Services\Gateway\GatewayOneService::class);
    });

    it('return another service sucessfully', function () {
        $gateway = Gateway::create(['name' => 'gateway_2', 'is_active' => true, 'priority' => 2]);
        $response = GatewayFactory::make($gateway);

        expect($response)->toBeInstanceOf(\App\Services\Gateway\GatewayTwoService::class);
    });


    it('should throw an exception for unsupported gateway', function () {
        $gateway = Gateway::create(['name' => 'unsupported_gateway', 'is_active' => true, 'priority' => 1]);

        expect(fn() => GatewayFactory::make($gateway))->toThrow(\InvalidArgumentException::class, "Unsupported gateway: unsupported_gateway");
    });
});
