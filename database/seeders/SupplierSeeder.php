<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'CV Maju Bersama Fashion',
                'email' => 'supplier1@majubersama.com',
                'phone' => '081234567890',
                'address' => 'Jl. Raya Bandung No. 10, Bandung',
            ],
            [
                'name' => 'PT Grosir Mode Indonesia',
                'email' => 'order@grosirmode.co.id',
                'phone' => '082345678901',
                'address' => 'Jl. Tanah Abang Blok A No. 5, Jakarta',
            ],
            [
                'name' => 'UD Sumber Kain Nusantara',
                'email' => 'info@sumberkain.com',
                'phone' => '083456789012',
                'address' => 'Jl. Pasar Baru No. 22, Surabaya',
            ],
            [
                'name' => 'Toko Grosir Pakaian Murah',
                'email' => 'grosir@pakaiancheap.com',
                'phone' => '084567890123',
                'address' => 'Jl. Cihampelas No. 88, Bandung',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
