<?php

use App\Http\Resources\GatewayResource;
use App\Models\Gateway;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Gateway Resource', function () {
    uses(RefreshDatabase::class);

    it('returns the correct structure', function () {
        $gateway = Gateway::create([
            'name' => 'gateway_1',
            'is_active' => true,
            'priority' => 1,
        ]);

        $resource = new GatewayResource($gateway);
        $data = $resource->toArray(request());

        expect($data)->toHaveKey('id');
        expect($data['name'])->toBe('gateway_1');
        expect($data['is_active'])->toBe(true);
        expect($data['priority'])->toBe(1);
        expect($data)->not()->toHaveKey('created_at');
        expect($data)->not()->toHaveKey('updated_at');
    });
});
