<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

describe('Product Update', function () {
    uses(RefreshDatabase::class);

    it('should update a Product successfully as admin', function () {
        $user = User::factory()->create(['role' => 'ADMIN']);
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
});
