<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Atasan', 'description' => 'Kategori produk atasan'],
            ['name' => 'Dress', 'description' => 'Kategori produk dress'],
            ['name' => 'Rok', 'description' => 'Kategori produk rok'],
            ['name' => 'Setelans', 'description' => 'Kategori produk setelans'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
