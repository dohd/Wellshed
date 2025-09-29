<?php

namespace App\Models\salary\Traits;

use App\Models\hrm\Hrm;
use App\Models\hrm\HrmMeta;
use App\Models\allowance_employee\AllowanceEmployee;

/**
 * ClasssalaryRelationship
 */
trait SalaryRelationship
{
     
    /**
     * Get the user that owns the SalaryRelationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Hrm::class, 'employee_id');
    }
    
    public function employee_allowance()
    {
        return $this->hasMany(AllowanceEmployee::class, 'contract_id');
    }
}
