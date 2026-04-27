<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            UnitSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,    // stok awal = 0
            PurchaseSeeder::class,   // stok bertambah
            SaleSeeder::class,       // stok berkurang
            ReturnSeeder::class,     // stok berkurang (return ke supplier)
            StockOpnameSeeder::class, // rekonsiliasi stok
        ]);
    }
}
