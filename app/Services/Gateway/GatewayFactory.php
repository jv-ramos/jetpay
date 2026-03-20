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
        return match ($gateway->name) {
            'gateway_1' => new GatewayOneService(
                new GatewayRequestService('http://localhost:3001')
            ),
            'gateway_2' => new GatewayTwoService(
                new GatewayRequestService('http://localhost:3002')
            ),
            default => throw new \InvalidArgumentException("Unsupported gateway: {$gateway->name}"),
        };
    }
}
