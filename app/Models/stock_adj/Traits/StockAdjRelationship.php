<?php

namespace App\Models\stock_adj\Traits;

use App\Models\account\Account;
use App\Models\stock_adj\StockAdjItem;
use App\Models\transaction\Transaction;

trait StockAdjRelationship
{    
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'stock_adj_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function items()
    {
        return $this->hasMany(StockAdjItem::class, 'stock_adj_id');
    }
}
