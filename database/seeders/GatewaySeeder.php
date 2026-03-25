<?php

namespace Database\Seeders;

use App\Models\Gateway;
use Illuminate\Database\Seeder;

class GatewaySeeder extends Seeder
{
    public function run(): void
    {
        Gateway::create(['name' => 'gateway_1', 'is_active' => 'true', 'priority' => 1]);
        Gateway::create(['name' => 'gateway_2', 'is_active' => 'true', 'priority' => 2]);
    }
}
