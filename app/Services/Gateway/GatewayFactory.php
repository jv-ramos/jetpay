<?php

namespace App\Services\Gateway;

use App\Models\Gateway;

class GatewayFactory
{
    /**
     * Create a new class instance.
     */
    public static function make(Gateway $gateway): GatewayInterface
    {
        return match ($gateway->name) {
            'gateway_1' => new GatewayOneService(),
            'gateway_2' => new GatewayTwoService(),
            default => throw new \InvalidArgumentException("Unsupported gateway: {$gateway->name}"),
        };
    }
}
