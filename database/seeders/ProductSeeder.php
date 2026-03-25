<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create(['name' => 'Product 1', 'amount' => 1000]);
        Product::create(['name' => 'Product 2', 'amount' => 2500]);
        Product::create(['name' => 'Product 3', 'amount' => 5000]);
    }
}
