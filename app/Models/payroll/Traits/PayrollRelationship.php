<?php

namespace App\Models\payroll\Traits;

use App\Models\payroll\PayrollItemV2;
use App\Models\utility_bill\UtilityBill;

/**
 * Class PayrollRelationship
 */
trait PayrollRelationship
{
   public function bills()
   {
      return $this->hasMany(UtilityBill::class);
   }

   public function payroll_items()
   {
      return $this->hasMany(PayrollItemV2::class, 'payroll_id', 'id');
   }
}
