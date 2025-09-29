<?php

namespace App\Models\bom\Traits;

use App\Models\bom\BoM;
use App\Models\boq\BoQItem;
use App\Models\product\ProductVariation;
use App\Models\productvariable\Productvariable;

/**
 * Class BoMItemRelationship
 */
trait BoMItemRelationship
{
    public function bom()
    {
        return $this->belongsTo(BoM::class, 'bom_id');
    }
    public function product()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }
    
    public function unit_of_measure()
    {
        return $this->belongsTo(Productvariable::class, 'unit_id');
    }

    public function boq_item()
    {
        return $this->belongsTo(BoQItem::class, 'boq_item_id');
    }
}
