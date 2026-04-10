<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

describe('Product Create', function () {
    uses(RefreshDatabase::class);

    dataset('roles', ['ADMIN', 'MANAGER', 'FINANCE', 'USER']);
    dataset('product', ['name', 'amount']);

    it('should fail to create a Product unauthenticated', function () {
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post('/api/products', [
                'name' => 'test_product',
                'amount' => 10,
            ]);

        $response->assertUnauthorized();
        expect(Product::where('name', 'test_product')->exists())->toBeFalse();
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

    it('should fail to create a Product with missing :dataset', function (string $product) {
        $data = $product === 'name' ? ['amount' => 10] : ['name' => 'test_product'];

        $user = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/products', [
                $data,
            ]);

        $response->assertUnprocessable();
    })->with('product');

    it('should create a Product successfully if user role is :dataset', function (string $role) {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/products', [
                'name' => 'test_product',
                'amount' => 10,
            ]);

        $response->assertCreated();
        expect(Product::where('name', 'test_product')->exists())->toBeTrue();
    })->with(['ADMIN', 'MANAGER', 'FINANCE']);
});
