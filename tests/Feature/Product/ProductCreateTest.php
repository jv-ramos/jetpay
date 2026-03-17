<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

describe('Product Create', function () {
    uses(RefreshDatabase::class);

    it('should fail to create a Product with missing name', function () {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/products', [
                'amount' => 10,
            ]);

        $response->assertUnprocessable();
    });

    it('should fail to create a Product with missing amount', function () {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/products', [
                'name' => 'test_product',
            ]);

        $response->assertUnprocessable();
    });

    it('should create a Product successfully as admin', function () {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/products', [
                'name' => 'test_product',
                'amount' => 10,
            ]);

        $response->assertCreated();
        expect(Product::where('name', 'test_product')->exists())->toBeTrue();
    });

    it('should create a Product successfully as manager', function () {
        $user = User::factory()->create(['role' => 'MANAGER']);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/products', [
                'name' => 'test_product',
                'amount' => 10,
            ]);

        $response->assertCreated();
        expect(Product::where('name', 'test_product')->exists())->toBeTrue();
    });

    it('should fail to create a Product as finance', function () {
        $user = User::factory()->create(['role' => 'FINANCE']);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/products', [
                'name' => 'test_product',
                'amount' => 10,
            ]);

        $response->assertCreated();
        expect(Product::where('name', 'test_product')->exists())->toBeTrue();
    });

    it('should fail to create a Product as regular user', function () {
        $user = User::factory()->create(['role' => 'USER']);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/products', [
                'name' => 'test_product',
                'amount' => 10,
            ]);

        $response->assertForbidden();
        expect(Product::where('name', 'test_product')->exists())->toBeFalse();
    });
});
