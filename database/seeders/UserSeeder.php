<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@jetpay.com',
            'password' => Hash::make('password'),
            'role'     => 'ADMIN',
        ]);

        User::create([
            'name'     => 'Finance User',
            'email'    => 'finance@jetpay.com',
            'password' => Hash::make('password'),
            'role'     => 'FINANCE',
        ]);

        User::create([
            'name'     => 'Manager User',
            'email'    => 'manager@jetpay.com',
            'password' => Hash::make('password'),
            'role'     => 'MANAGER',
        ]);
    }
}
