<?php

namespace App\Models\key_activity\Traits;

use App\Models\employeeDailyLog\EmployeeTaskSubcategories;
use App\Models\key_activity\KeyActivity;

/**
 * Class KeyActivityRelationship
 */
trait KeyActivityRelationship
{
    public function subcategories()
    {
        return $this->hasMany(EmployeeTaskSubcategories::class, 'key_activity_id');
    }
}
