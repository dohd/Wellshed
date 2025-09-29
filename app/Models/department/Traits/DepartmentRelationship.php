<?php

namespace App\Models\department\Traits;

use App\Models\hrm\Hrm;
use App\Models\hrm\HrmMeta;
use App\Models\PurchaseClassBudgets\PurchaseClassBudget;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class DepartmentRelationship
 */
trait DepartmentRelationship
{
      public function users()
    {
        return $this->hasManyThrough(Hrm::class, HrmMeta::class, 'department_id', 'id', 'id', 'user_id');
    }

    public function purchaseClassBudgets(): HasMany{

          return $this->hasMany(PurchaseClassBudget::class, 'department_id', 'id');
    }
}
