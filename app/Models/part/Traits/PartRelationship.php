<?php

namespace App\Models\part\Traits;

use App\Models\part\PartItem;
use App\Models\product\ProductVariation;
use App\Models\standard_template\StandardTemplate;

/**
 * Class PartRelationship
 */
trait PartRelationship
{
    public function part_items()
    {
        return $this->hasMany(PartItem::class);
    }

    public function productvar()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }
    public function template()
    {
        return $this->belongsTo(StandardTemplate::class, 'template_id');
    }
}
