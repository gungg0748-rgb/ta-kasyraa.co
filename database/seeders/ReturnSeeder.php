<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\ReturnItem;
use App\Models\StockReturn;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReturnSeeder extends Seeder
{
    public function run(): void
    {
        $gudang = User::where('role', 'gudang')->first();

        // Ambil 2 purchase untuk di-return sebagian
        $purchases = Purchase::with(['items.variant', 'supplier'])->take(2)->get();

        foreach ($purchases as $ri => $purchase) {
            $stockReturn = StockReturn::create([
                'return_number' => 'RTN-' . date('Ymd') . '-' . str_pad($ri + 1, 4, '0', STR_PAD_LEFT),
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'user_id' => $gudang->id,
                'date' => now()->subDays(rand(1, 7))->format('Y-m-d'),
                'notes' => 'Barang cacat / tidak sesuai pesanan',
            ]);

            // Return 1-2 item dari purchase tersebut
            $itemsToReturn = $purchase->items->take(rand(1, 2));

            foreach ($itemsToReturn as $purchaseItem) {
                $qtyReturn = rand(1, min(3, $purchaseItem->qty));

                ReturnItem::create([
                    'return_id' => $stockReturn->id,
                    'variant_id' => $purchaseItem->variant_id,
                    'qty' => $qtyReturn,
                    'reason' => 'Barang tidak sesuai spesifikasi',
                ]);

                // Kurangi stok karena barang dikembalikan ke supplier
                $purchaseItem->variant->decrement('stock', $qtyReturn);
            }
        }
    }
}
