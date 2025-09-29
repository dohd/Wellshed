<?php

namespace App\Models\part\Traits;

use App\Models\part\Part;
use App\Models\product\Product;
use App\Models\product\ProductVariation;
use App\Models\productvariable\Productvariable;

/**
 * Class PartItemRelationship
 */
trait PartItemRelationship
{
    public function part()
    {
        return $this->belongsTo(Part::class,'part_id');
    }
    public function product()
    {
        return $this->belongsTo(ProductVariation::class,'product_id');
    }
    public function unit()
    {
        return $this->belongsTo(Productvariable::class,'unit_id');
    }
}
