<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin utama
        User::create([
            'name' => 'Admin Kasyraa',
            'email' => 'admin@kasyraa.co',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Kasir
        User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir@kasyraa.co',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        // Gudang
        User::create([
            'name' => 'Gudang 1',
            'email' => 'gudang@kasyraa.co',
            'password' => Hash::make('password'),
            'role' => 'gudang',
            'is_active' => true,
        ]);
    }
}
