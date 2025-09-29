<?php

namespace App\Models\items\Traits;

use App\Models\product\ProductVariation;
use App\Models\stock_rcv\StockRcvItem;
use App\Models\stock_transfer\StockTransfer;

/**
 * Class CustomerRelationship
 */
trait StockTransferItemRelationship
{
    public function stock_transfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function rcv_items()
    {
        return $this->hasMany(StockRcvItem::class, 'transf_item_id');
    }

    public function productvar()
    {
        return $this->belongsTo(ProductVariation::class, 'productvar_id');
    }
}
