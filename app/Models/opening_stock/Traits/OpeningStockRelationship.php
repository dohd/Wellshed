<?php

namespace App\Models\opening_stock\Traits;

use App\Models\account\Account;
use App\Models\items\OpeningStockItem;

trait OpeningStockRelationship
{    
    public function items()
    {
        return $this->hasMany(OpeningStockItem::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
