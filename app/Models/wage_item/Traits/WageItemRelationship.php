<?php

namespace App\Models\wage_item\Traits;

use App\Models\casual\CasualLabourer;
use App\Models\casual_labourer_remuneration\CLRWageItem;

/**
 * Class WageItemRelationship
 */
trait WageItemRelationship
{
   public function clrWageItems()
   {
      return $this->hasMany(CLRWageItem::class);
   }

   public function casualLabourers()
   {
		return $this->belongsToMany(CasualLabourer::class, 'casual_wage_item', 'wage_item_id', 'casual_id');
   }
}
