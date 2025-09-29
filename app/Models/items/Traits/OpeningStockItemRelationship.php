<?php

namespace App\Models\items\Traits;

use App\Models\opening_stock\OpeningStock;
use App\Models\product\Product;
use App\Models\product\ProductVariation;

trait OpeningStockItemRelationship
{
    public function opening_stock()
    {
        return $this->belongsTo(OpeningStock::class);
    }

    public function productvariation()
    {
        return $this->belongsTo(ProductVariation::class, 'productvar_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }
}                                                        
