<?php

namespace App\Models\rfq_analysis\Traits;

use App\Models\product\ProductVariation;
use App\Models\rfq\RfQItem;

trait RfQAnalysisItemRelationship
{
   public function rfq_item()
   {
    return $this->belongsTo(RfQItem::class, 'rfq_item_id');
   }
   public function product()
   {
    return $this->belongsTo(ProductVariation::class, 'product_id');
   }
}
