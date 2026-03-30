<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::create(['name' => 'Client One', 'email' => 'client1@example.com']);
        Client::create(['name' => 'Client Two', 'email' => 'client2@example.com']);
        Client::create(['name' => 'Client Three', 'email' => 'client3@example.com']);
    }
}
