<?php

namespace App\Models\standard_template\Traits;

use App\Models\product\ProductVariation;
use App\Models\productvariable\Productvariable;
use App\Models\standard_template\StandardTemplate;

/**
 * Class StandardTemplateItemRelationship
 */
trait StandardTemplateItemRelationship
{
    public function standard_template()
    {
        return $this->belongsTo(StandardTemplate::class,'standard_template_id');
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
