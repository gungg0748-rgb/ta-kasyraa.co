<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameItem extends Model
{
    protected $fillable = ['opname_id', 'variant_id', 'system_stock', 'physical_stock'];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class, 'opname_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function getDifferenceAttribute(): int
    {
        return $this->physical_stock - $this->system_stock;
    }
}
