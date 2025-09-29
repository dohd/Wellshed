<?php

namespace App\Models\sale_return\Traits;

use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\quote\Quote;
use App\Models\sale_return\SaleReturnItem;
use App\Models\transaction\Transaction;

trait SaleReturnRelationship
{    
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }
}
