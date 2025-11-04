<?php

namespace App\Models\stock_transaction\Traits;

use App\Models\product\ProductVariation;

/**
 * Class StockTransactionRelationship
 */
trait StockTransactionRelationship
{
      public function product()
      {
        return $this->belongsTo(ProductVariation::class, 'stock_item_id');
      }
}
