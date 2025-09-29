<?php

namespace App\Models\sale_return\Traits;

use App\Models\items\VerifiedItem;
use App\Models\product\Product;
use App\Models\product\ProductVariation;
use App\Models\quote\Quote;
use App\Models\sale_return\SaleReturn;
use App\Models\warehouse\Warehouse;

trait SaleReturnItemRelationship
{    
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function sale_return()
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function verified_item()
    {
        return $this->belongsTo(VerifiedItem::class, 'verified_item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productvar()
    {
        return $this->belongsTo(ProductVariation::class, 'productvar_id');
    }
}
