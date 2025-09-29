<?php

namespace App\Models\leave\Traits;

use App\Models\Access\User\User;
use App\Models\hrm\Hrm;
use App\Models\leave\LeaveApprover;
use App\Models\leave_category\LeaveCategory;

trait LeaveRelationship
{
    public function employee()
    {
        return $this->belongsTo(Hrm::class, 'employee_id');
    }

    public function assist_employee()
    {
        return $this->belongsTo(Hrm::class, 'assist_employee_id');
    }

    public function leave_category()
    {
        return $this->belongsTo(LeaveCategory::class);
    }

    public function approvers()
    {
        return $this->hasMany(LeaveApprover::class, 'leave_id');
    }
}
