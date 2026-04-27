<?php

namespace Database\Seeders;

use App\Models\OpnameItem;
use App\Models\ProductVariant;
use App\Models\StockOpname;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockOpnameSeeder extends Seeder
{
    public function run(): void
    {
        $gudang = User::where('role', 'gudang')->first();
        $variants = ProductVariant::all();

        // 1 opname sudah confirmed (historis)
        $opname1 = StockOpname::create([
            'user_id' => $gudang->id,
            'date' => now()->subDays(30)->format('Y-m-d'),
            'notes' => 'Stok opname bulanan periode lalu',
            'status' => 'confirmed',
        ]);

        foreach ($variants->take(20) as $variant) {
            // Simulasi selisih kecil: fisik bisa lebih atau kurang 1-2 dari sistem
            $systemStock = $variant->stock;
            $diff = rand(-2, 2);
            $physicalStock = max(0, $systemStock + $diff);

            OpnameItem::create([
                'opname_id' => $opname1->id,
                'variant_id' => $variant->id,
                'system_stock' => $systemStock,
                'physical_stock' => $physicalStock,
            ]);
        }

        // 1 opname masih draft (sedang berjalan)
        $opname2 = StockOpname::create([
            'user_id' => $gudang->id,
            'date' => now()->format('Y-m-d'),
            'notes' => 'Stok opname bulan ini',
            'status' => 'draft',
        ]);

        foreach ($variants->skip(20)->take(15) as $variant) {
            $systemStock = $variant->stock;
            $diff = rand(-1, 1);
            $physicalStock = max(0, $systemStock + $diff);

            OpnameItem::create([
                'opname_id' => $opname2->id,
                'variant_id' => $variant->id,
                'system_stock' => $systemStock,
                'physical_stock' => $physicalStock,
            ]);
        }
    }
}
