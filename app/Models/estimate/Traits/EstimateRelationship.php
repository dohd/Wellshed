<?php

namespace App\Models\estimate\Traits;

use App\Models\customer\Customer;
use App\Models\estimate\EstimateItem;
use App\Models\invoice\Invoice;
use App\Models\quote\Quote;

trait EstimateRelationship
{    
   public function invoice() 
   {
    return $this->hasOne(Invoice::class);
   }

   public function quote() 
   {
    return $this->belongsTo(Quote::class);
   }

   public function customer() 
   {
    return $this->belongsTo(Customer::class);
   }

   public function items() 
   {
    return $this->hasMany(EstimateItem::class);
   }
}
