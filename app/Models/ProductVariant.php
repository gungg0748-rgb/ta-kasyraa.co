<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{

    protected $fillable = ['product_id', 'model', 'color', 'size', 'stock', 'barcode'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'variant_id');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'variant_id');
    }

    public function returnItems()
    {
        return $this->hasMany(ReturnItem::class, 'variant_id');
    }

    public function opnameItems()
    {
        return $this->hasMany(OpnameItem::class, 'variant_id');
    }
}
