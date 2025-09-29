<?php

namespace App\Models\withholding\Traits;

use App\Models\items\WithholdingItem;
use App\Models\transaction\Transaction;

/**
 * Class WithholdingRelationship
 */
trait WithholdingRelationship
{
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'wht_id');
    }

    public function items()
    {
        return $this->hasMany(WithholdingItem::class, 'withholding_id');
    }

    public function account()
    {
        return $this->belongsTo('App\Models\account\Account');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\customer\Customer');
    }
}
