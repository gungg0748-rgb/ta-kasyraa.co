<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = ['Pcs', 'Lusin', 'Kodi', 'Gross', 'Set', 'Pack'];

        foreach ($units as $unit) {
            Unit::create(['name' => $unit]);
        }
    }
}
