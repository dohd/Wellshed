<?php

namespace App\Models\stock_rcv\Traits;

use App\Models\items\StockTransferItem;
use App\Models\product\ProductVariation;
use App\Models\stock_rcv\StockRcv;

trait StockRcvItemRelationship
{
    public function stock_rcv()
    {
        return $this->belongsTo(StockRcv::class, 'stock_rcv_id');
    }

    public function transfer_item()
    {
        return $this->belongsTo(StockTransferItem::class, 'transf_item_id');
    }

    public function productvar()
    {
        return $this->belongsTo(ProductVariation::class, 'productvar_id');
    }
    public function productvar2()
    {
        return $this->belongsTo(ProductVariation::class, 'item_id');
    }
}
