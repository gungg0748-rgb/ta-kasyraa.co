<?php

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $kasir = User::where('role', 'kasir')->first();

        for ($i = 1; $i <= 10; $i++) {
            $date = now()->subDays(rand(1, 60))->format('Y-m-d');

            // Query fresh setiap iterasi agar stok yang sudah berkurang ikut terhitung
            $availableVariants = ProductVariant::with('product')->where('stock', '>=', 2)->get();

            if ($availableVariants->isEmpty()) {
                continue;
            }

            $sale = Sale::create([
                'invoice_number' => 'INV-' . date('Ymd', strtotime($date)) . '-' . str_pad($i, 4, '0', STR_PAD_LEFT) . rand(10, 99),
                'user_id' => $kasir->id,
                'total' => 0,
                'date' => $date,
                'notes' => null,
            ]);

            $total = 0;
            $pickCount = min(rand(1, 3), $availableVariants->count());
            $selectedVariants = $availableVariants->random($pickCount);

            foreach ($selectedVariants as $variant) {
                // Refresh stok dari DB untuk akurasi
                $variant->refresh();

                if ($variant->stock < 1) {
                    continue;
                }

                $qty = rand(1, min(2, $variant->stock));
                $price = $variant->product->price;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'variant_id' => $variant->id,
                    'qty' => $qty,
                    'price' => $price,
                ]);

                // Kurangi stok sesuai penjualan
                $variant->decrement('stock', $qty);

                $total += $qty * $price;
            }

            $sale->update(['total' => $total]);
        }
    }
}
