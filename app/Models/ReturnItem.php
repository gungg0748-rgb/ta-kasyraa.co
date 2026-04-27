<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    protected $table = 'return_items';

    protected $fillable = ['return_id', 'variant_id', 'qty', 'reason'];

    public function stockReturn()
    {
        return $this->belongsTo(StockReturn::class, 'return_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
