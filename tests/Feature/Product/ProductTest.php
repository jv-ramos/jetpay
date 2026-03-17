<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

describe('Product', function () {
    uses(RefreshDatabase::class);

    it("shouldn't be able to access products without authentication", function () {
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->get('/api/products');

        $response->assertUnauthorized();
    });

    it('should index products successfully to any user', function () {
        $user = User::factory()->create();
        Product::create([
            'name' => 'test_product',
            'amount' => 10,
        ]);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->get('/api/products');

        $response->assertOk();
        expect($response->json())->toHaveKey('data');
        expect($response->json()['data'])->toHaveCount(1);
        expect($response->json()['data'][0]['name'])->toBe('test_product');
        expect($response->json()['data'][0]['amount'])->toBe(10);
    });

    it('should show a product successfully to any user', function () {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'test_product',
            'amount' => 10,
        ]);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->get("/api/products/{$product->id}");

        $response->assertOk();
        expect($response->json())->toHaveKey('data');
        expect($response->json()['data']['name'])->toBe('test_product');
        expect($response->json()['data']['amount'])->toBe(10);
    });

    it('should fail to delete a Product without authentication', function () {
        $product = Product::create([
            'name' => 'test_product',
            'amount' => 10,
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->delete("/api/products/{$product->id}");

        $response->assertUnauthorized();
    });

    it('should fail to delete a Product as a common user', function () {
        $user = User::factory()->create(['role' => 'USER']);
        $product = Product::create([
            'name' => 'test_product',
            'amount' => 10,
        ]);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->delete("/api/products/{$product->id}");

        $response->assertForbidden();
    });

    it('should delete a Product successfully as admin', function () {
        $user = User::factory()->create(['role' => 'ADMIN']);
        $product = Product::create([
            'name' => 'test_product',
            'amount' => 10,
        ]);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->delete("/api/products/{$product->id}");

        $response->assertNoContent();
        expect(Product::find($product->id))->toBeNull();
    });

    it('should delete a Product successfully as manager', function () {
        $user = User::factory()->create(['role' => 'MANAGER']);
        $product = Product::create([
            'name' => 'test_product',
            'amount' => 10,
        ]);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->delete("/api/products/{$product->id}");

        $response->assertNoContent();
        expect(Product::find($product->id))->toBeNull();
    });
});
