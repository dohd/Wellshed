<?php

namespace App\Models\items\Traits;

use App\Models\estimate\EstimateItem;
use App\Models\product\ProductVariation;
use App\Models\quote\Quote;

trait VerifiedItemRelationship
{
   public function est_items()
   {
      return $this->hasMany(EstimateItem::class, 'vrf_item_id');
   }

   public function product_variation()
   {
      return $this->belongsTo(ProductVariation::class, 'product_id');
   }

   public function quote()
   {
      return $this->belongsTo(Quote::class, 'quote_id');
   }
}
