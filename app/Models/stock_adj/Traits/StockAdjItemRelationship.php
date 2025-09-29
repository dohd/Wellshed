<?php

namespace App\Models\stock_adj\Traits;

use App\Models\product\ProductVariation;
use App\Models\stock_adj\StockAdj;

trait StockAdjItemRelationship
{    
    public function productvar()
    {
        return $this->belongsTo(ProductVariation::class, 'productvar_id');
    }

    public function stock_adj()
    {
        return $this->belongsTo(StockAdj::class);
    }
}
