<?php

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $gudang = User::where('role', 'gudang')->first();
        $suppliers = Supplier::all();
        $variants = ProductVariant::with('product')->get();
        $maxPick = min(8, $variants->count());

        for ($i = 1; $i <= 5; $i++) {
            $supplier = $suppliers->random();
            $date = now()->subDays(rand(10, 90))->format('Y-m-d');

            $purchase = Purchase::create([
                'invoice_number' => 'PO-' . date('Ymd', strtotime($date)) . '-' . str_pad($i, 4, '0', STR_PAD_LEFT) . rand(10, 99),
                'supplier_id' => $supplier->id,
                'user_id' => $gudang->id,
                'total' => 0,
                'date' => $date,
                'notes' => 'Pembelian stok periode ' . $i,
            ]);

            $total = 0;
            $pickCount = rand(4, $maxPick);
            $selectedVariants = $variants->random($pickCount);

            foreach ($selectedVariants as $variant) {
                $qty = rand(5, 20);
                $price = $variant->product->price * 0.7; // harga beli 70% dari harga jual

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'variant_id' => $variant->id,
                    'qty' => $qty,
                    'price' => $price,
                ]);

                // Tambah stok sesuai pembelian
                $variant->increment('stock', $qty);
                $variant->stock += $qty; // update in-memory agar SaleSeeder baca stok terbaru

                $total += $qty * $price;
            }

            $purchase->update(['total' => $total]);
        }
    }
}
