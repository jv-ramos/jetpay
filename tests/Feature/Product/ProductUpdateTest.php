<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

describe('Product Update', function () {
    uses(RefreshDatabase::class);

    dataset('roles', ['ADMIN', 'MANAGER', 'FINANCE', 'USER']);

    it('should fail to update a Product unauthenticated', function () {
        $product = Product::create([
            'name' => 'test_product',
            'amount' => 10,
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->put("/api/products/{$product->id}", [
                'name' => 'updated_product',
                'amount' => 20,
            ]);

        $response->assertUnauthorized();
        expect(Product::find($product->id)->name)->toBe('test_product');
    });

    it('should fail to update a Product as regular user', function () {
        $user = User::factory()->create(['role' => 'USER']);
        $product = Product::create([
            'name' => 'test_product',
            'amount' => 10,
        ]);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->put("/api/products/{$product->id}", [
                'name' => 'updated_product',
                'amount' => 20,
            ]);

        $response->assertForbidden();
        expect(Product::find($product->id)->name)->toBe('test_product');
    });

    it('should update a Product successfully if user role is :dataset', function (string $role) {
        $user = User::factory()->create(['role' => $role]);
        $product = Product::create([
            'name' => 'test_product',
            'amount' => 10,
        ]);

        $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->put("/api/products/{$product->id}", [
                'name' => 'updated_product',
                'amount' => 20,
            ])->assertOk();

        $updatedProduct = Product::find($product->id);
        expect($updatedProduct)->exists()->toBeTrue();
        expect($updatedProduct->amount)->toBe(20);
    });
})->with(['ADMIN', 'MANAGER', 'FINANCE']);
