<?php

namespace App\Models\payroll\Traits;

use App\Models\hrm\Hrm;
use App\Models\hrm\HrmMeta;
use App\Models\jobtitle\JobTitle;
use App\Models\salary\Salary;
use App\Models\payroll\PayrollItem;
use App\Models\payroll\Payroll;

/**
 * Class PayrollRelationship
 */
trait PayrollItemRelationship
{
     public function payroll()
     {
        return $this->belongsTo(Payroll::class);
     }
     public function employee()
     {
         return $this->belongsTo(Hrm::class, 'employee_id', 'id');
     }
     public function hrmmetas()
     {
         return $this->hasOne(HrmMeta::class, 'user_id', 'employee_id');
     }
     
     public function salary()
     {
         return $this->hasOne(Salary::class, 'employee_id', 'employee_id');
     }
     
}
