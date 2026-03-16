<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('Product', function () {
    uses(RefreshDatabase::class);

    it('should fail to create a Product with missing name', function () {
        expect(fn() => Product::create(['amount' => 1]))
            ->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('should fail to create a Product with missing amount', function () {
        expect(fn() => Product::create(['name' => 'test_product']))
            ->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('should create a Product successfully', function () {
        $product = Product::create(['name' => 'test_product', 'amount' => 10]);

        expect($product)->toBeInstanceOf(Product::class);
        expect($product->name)->toBe('test_product');
        expect($product->amount)->toBe(10);
    });
});
