<?php

namespace App\Models\lpo\Traits;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\quote\Quote;

trait LpoRelationship
{
     public function quotes()
     {
          return $this->hasMany(Quote::class);
     }

     public function customer()
     {
          return $this->belongsTo(Customer::class);
     }

     public function branch()
     {
          return $this->belongsTo(Branch::class);
     }
}
