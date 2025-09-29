<?php

namespace App\Models\stock_rcv\Traits;

use App\Models\hrm\Hrm;
use App\Models\stock_rcv\StockRcvItem;
use App\Models\stock_transfer\StockTransfer;

trait StockRcvRelationship
{
    public function stock_transfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function receiver()
    {
        return $this->belongsTo(Hrm::class, 'receiver_id');
    }

    public function items()
    {
        return $this->hasMany(StockRcvItem::class);
    }
}
