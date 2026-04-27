<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $pcs = Unit::where('name', 'Pcs')->first();

        $this->clearExistingProductData();

        $products = [
            [
                'name' => 'Sabrina Fit On Body Super Stretch', 'category' => 'Atasan', 'price' => 89000, 'reorder_level' => 5,
                'variants' => [
                    ['model' => 'Lengan Pendek', 'color' => 'Black', 'size' => 'One Size (Panjang 89 Cm)'],
                    ['model' => 'Lengan Pendek', 'color' => 'Pink', 'size' => 'One Size (Panjang 89 Cm)'],
                    ['model' => 'Lengan Pendek', 'color' => 'Blue', 'size' => 'One Size (Panjang 89 Cm)'],
                    ['model' => 'Lengan Pendek', 'color' => 'Wine', 'size' => 'One Size (Panjang 89 Cm)'],
                ],
            ],
            [
                'name' => 'Sabrina Fit On Body Super Stretch', 'category' => 'Atasan', 'price' => 99000, 'reorder_level' => 5,
                'variants' => [
                    ['model' => 'Lengan Panjang', 'color' => 'Black', 'size' => 'One Size (Panjang 99 Cm)'],
                    ['model' => 'Lengan Panjang', 'color' => 'White', 'size' => 'One Size (Panjang 99 Cm)'],
                ],
            ],
            [
                'name' => 'Tanktop Bodysuit', 'category' => 'Atasan', 'price' => 109000, 'reorder_level' => 5,
                'variants' => [
                    ['model' => null, 'color' => 'Blue', 'size' => null],
                    ['model' => null, 'color' => 'Pink', 'size' => null],
                    ['model' => null, 'color' => 'Wine', 'size' => null],
                ],
            ],
            [
                'name' => 'Zeta Top Linen', 'category' => 'Atasan', 'price' => 129000, 'reorder_level' => 5,
                'variants' => [
                    ['model' => 'Linen', 'color' => 'Beige', 'size' => 'One Size (Ld 75-100 Cm, Panjang 45 Cm)'],
                    ['model' => 'Linen', 'color' => 'White', 'size' => 'One Size (Ld 75-100 Cm, Panjang 45 Cm)'],
                    ['model' => 'Linen', 'color' => 'Moca', 'size' => 'One Size (Ld 75-100 Cm, Panjang 45 Cm)'],
                ],
            ],
            [
                'name' => 'Harua Halter Dress', 'category' => 'Dress', 'price' => 134000, 'reorder_level' => 5,
                'variants' => [
                    ['model' => null, 'color' => 'Brown', 'size' => 'One Size (Lingkar Dada 74-100 Cm, Panjang 135 Cm)'],
                    ['model' => null, 'color' => 'White', 'size' => 'One Size (Lingkar Dada 74-100 Cm, Panjang 135 Cm)'],
                ],
            ],
            [
                'name' => 'Athala Dress', 'category' => 'Dress', 'price' => 139000, 'reorder_level' => 5,
                'variants' => [
                    ['model' => null, 'color' => 'Nude', 'size' => null],
                ],
            ],
            [
                'name' => 'Maxi Skirt Denim Good Quality', 'category' => 'Rok', 'price' => 89000, 'reorder_level' => 5,
                'variants' => [
                    ['model' => 'Denim', 'color' => 'Light Blue', 'size' => null],
                ],
            ],
            [
                'name' => 'Skirt Patern |Summer Skirt | Rok Musim Panas', 'category' => 'Rok', 'price' => 69000, 'reorder_level' => 5,
                'variants' => [
                    ['model' => 'Summer Skirt', 'color' => 'Spirall', 'size' => null],
                ],
            ],
            [
                'name' => 'Laurent Set', 'category' => 'Setelans', 'price' => 189000, 'reorder_level' => 5,
                'variants' => [
                    ['model' => 'Linen', 'color' => 'Brown', 'size' => 'One Size (Ld 80-100 Cm, Panjang 50 Cm)'],
                ],
            ],
            [
                'name' => 'Laurent Set', 'category' => 'Setelans', 'price' => 209000, 'reorder_level' => 5,
                'variants' => [
                    ['model' => 'Crepe', 'color' => 'Black', 'size' => 'One Size (Ld 80-100 Cm, Panjang 50 Cm)'],
                    ['model' => 'Crepe', 'color' => 'Oat', 'size' => 'One Size (Ld 80-100 Cm, Panjang 50 Cm)'],
                ],
            ],
            [
                'name' => 'Bianca Set', 'category' => 'Setelans', 'price' => 149000, 'reorder_level' => 5,
                'variants' => [
                    ['model' => null, 'color' => 'White', 'size' => 'One Size (Ld 80-100 Cm, Panjang 50 Cm)'],
                    ['model' => null, 'color' => 'Brown', 'size' => 'One Size (Ld 80-100 Cm, Panjang 50 Cm)'],
                ],
            ],
        ];

        foreach ($products as $index => $data) {
            $category = Category::firstOrCreate(
                ['name' => $data['category']],
                ['description' => 'Kategori produk ' . strtolower($data['category'])]
            );

            $product = Product::create([
                'name' => $data['name'],
                'category_id' => $category->id,
                'unit_id' => $pcs->id,
                'price' => $data['price'],
                'reorder_level' => $data['reorder_level'],
                'barcode' => '8' . str_pad($index + 1, 12, '0', STR_PAD_LEFT),
                'description' => null,
                'image' => null,
            ]);

            foreach ($data['variants'] as $vi => $variant) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'model' => $variant['model'],
                    'color' => $variant['color'],
                    'size' => $variant['size'],
                    'stock' => 0, // stok awal 0, akan bertambah dari PurchaseSeeder
                    'barcode' => '9' . str_pad($product->id, 6, '0', STR_PAD_LEFT) . str_pad($vi + 1, 5, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }

    private function clearExistingProductData(): void
    {
        foreach ([
            'opname_items',
            'stock_opnames',
            'return_items',
            'returns',
            'sale_items',
            'sales',
            'purchase_items',
            'purchases',
            'product_variants',
            'products',
        ] as $table) {
            DB::table($table)->delete();
        }
    }
}
