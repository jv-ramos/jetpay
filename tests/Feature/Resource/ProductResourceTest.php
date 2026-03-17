<?php

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Product Resource', function () {
    uses(RefreshDatabase::class);

    it('should return the correct product data in the resource', function () {
        $product = Product::create([
            'name' => 'test_product',
            'amount' => 10,
        ]);

        $resource = new ProductResource($product);
        $data = $resource->toArray(request());

        expect($data)->toHaveKey('id');
        expect($data['name'])->toBe('test_product');
        expect($data['amount'])->toBe(10);
        expect($data)->not()->toHaveKey('created_at');
        expect($data)->not()->toHaveKey('updated_at');
    });
});
