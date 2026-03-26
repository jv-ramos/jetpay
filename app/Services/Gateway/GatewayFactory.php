<?php

namespace App\Services\Gateway;

use App\Models\Gateway;
use App\Services\Gateway\GatewayRequestService;

class GatewayFactory
{
    /**
     * Create a new class instance.
     */
    public static function make(Gateway $gateway): GatewayInterface
    {
        $urls = [
            'gateway_1' => env('GATEWAY1_URL', 'http://localhost:3001'),
            'gateway_2' => env('GATEWAY2_URL', 'http://localhost:3002'),
        ];

        return match ($gateway->name) {
            'gateway_1' => new GatewayOneService(
                new GatewayRequestService($urls['gateway_1'])
            ),
            'gateway_2' => new GatewayTwoService(
                new GatewayRequestService($urls['gateway_2'])
            ),
            default => throw new \InvalidArgumentException("Unsupported gateway: {$gateway->name}"),
        };
    }
}
