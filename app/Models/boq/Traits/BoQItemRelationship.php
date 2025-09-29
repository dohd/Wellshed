<?php

namespace App\Models\boq\Traits;

use App\Models\boq\BoQ;
use App\Models\product\ProductVariation;
use App\Models\boq\BoQSheet;

/**
 * Class BoQItemRelationship
 */
trait BoQItemRelationship
{
    public function boq()
    {
        return $this->belongsTo(BoQ::class, 'boq_id');
    }
    public function product()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }

    public function sheet()
    {
        return $this->belongsTo(BoQSheet::class, 'boq_sheet_id');
    }
}
