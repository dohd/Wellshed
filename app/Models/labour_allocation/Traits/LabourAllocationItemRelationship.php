<?php

namespace App\Models\labour_allocation\Traits;

use App\Models\hrm\Hrm;
use App\Models\labour_allocation\LabourAllocation;

trait LabourAllocationItemRelationship
{
  public function employee()
  {
     return $this->belongsTo(Hrm::class, 'employee_id');
  }

  public function labour()
  {
     return $this->belongsTo(LabourAllocation::class, 'labour_id');
  }
}
