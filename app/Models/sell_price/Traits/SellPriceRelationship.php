<?php

namespace App\Models\sell_price\Traits;

use App\Models\import_request\ImportRequest;
use App\Models\sell_price\SellPriceItem;

/**
 * Class SellPriceRelationship
 */
trait SellPriceRelationship
{

    public function items()
    {
        return $this->hasMany(SellPriceItem::class, 'sell_price_id');
    }

    public function import_request()
    {
        return $this->belongsTo(ImportRequest::class, 'import_request_id');
    }
}
