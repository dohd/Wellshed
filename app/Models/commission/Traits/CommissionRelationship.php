<?php

namespace App\Models\commission\Traits;

use App\Models\commission\CommissionItem;
use App\Models\utility_bill\UtilityBill;

/**
 * Class CommissionRelationship
 */
trait CommissionRelationship
{
     public function items()
     {
        return $this->hasMany(CommissionItem::class, 'commission_id');
     }

     public function bill()
     {
         return $this->hasOne(UtilityBill::class);
     }
}
