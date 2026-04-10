<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

describe('Product', function () {
    uses(RefreshDatabase::class);

    dataset('roles', ['ADMIN', 'MANAGER', 'FINANCE', 'USER']);

    it("should be able to index Products without authentication", function () {
        Product::create([
            'name' => 'test_product',
            'amount' => 10,
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->get('/api/products');

        $response->assertOk();
    });

    it('should index Products successfully to :dataset', function (string $role) {
        $user = User::factory()->create(['role' => $role]);
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
    })->with('roles');

    it("should be able to show Products without authentication", function () {
        Product::create([
            'name' => 'test_product',
            'amount' => 10,
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->get('/api/products/1');

        $response->assertOk();
    });

    it('should show a product successfully if user role is :dataset', function (string $role) {
        $user = User::factory()->create(['role' => $role]);
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
    })->with('roles');

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

    it('should delete a Product successfully if user role is :dataset', function (string $role) {
        $user = User::factory()->create(['role' => $role]);
        $product = Product::create([
            'name' => 'test_product',
            'amount' => 10,
        ]);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->delete("/api/products/{$product->id}");

        $response->assertNoContent();
        expect(Product::find($product->id))->toBeNull();
    })->with(['ADMIN', 'MANAGER', 'FINANCE']);

    it('should belong to many transactions', function () {
        $product = Product::create([
            'name' => 'test_product',
            'amount' => 10,
        ]);

        expect($product->transactions())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
    });
});
