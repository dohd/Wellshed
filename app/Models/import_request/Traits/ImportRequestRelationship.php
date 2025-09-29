<?php

namespace App\Models\import_request\Traits;

use App\Models\currency\Currency;
use App\Models\import_request\ImportRequestExpense;
use App\Models\import_request\ImportRequestItem;
use App\Models\supplier\Supplier;

/**
 * Class ImportRequestRelationship
 */
trait ImportRequestRelationship
{
     public function supplier()
     {
        return $this->belongsTo(Supplier::class, 'supplier_id');
     }
     public function items()
     {
        return $this->hasMany(ImportRequestItem::class, 'import_request_id');
     }
     public function expenses()
     {
        return $this->hasMany(ImportRequestExpense::class, 'import_request_id');
     }
     public function currency()
     {
        return $this->belongsTo(Currency::class, 'currency_id');
     }
}
