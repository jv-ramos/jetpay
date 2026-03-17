<?php

use App\Models\Gateway;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Gateway', function () {

    uses(RefreshDatabase::class);

    it('can be created', function () {

        $gateway = Gateway::create([
            'name' => 'Gateway 1',
            'is_active' => true,
            'priority' => 1,
        ]);

        expect($gateway)->toBeInstanceOf(Gateway::class);
        expect($gateway->name)->toBe('Gateway 1');
        expect($gateway->is_active)->toBeTrue();
        expect($gateway->priority)->toBe(1);
    });

    it('should require a name', function () {
        expect(fn() => Gateway::create([
            'is_active' => true,
            'priority' => 1,
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('should toggle is_active successfully', function () {
        $gateway = Gateway::create([
            'name' => 'Gateway 1',
            'is_active' => true,
            'priority' => 1,
        ]);

        $this->patch("/api/gateways/{$gateway->id}/toggle")
            ->assertOk();

        $gateway->refresh();

        expect($gateway->is_active)->toBeFalse();
    });

    it('should update priority successfully', function () {
        $gateway = Gateway::create([
            'name' => 'Gateway 1',
            'is_active' => true,
            'priority' => 1,
        ]);

        $this->patch("/api/gateways/{$gateway->id}/priority", ['priority' => 2])
            ->assertOk();

        $gateway->refresh();

        expect($gateway->priority)->toBe(2);
    });
});
