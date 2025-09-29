<?php

namespace App\Models\attendance\Traits;

use App\Models\Access\User\User;
use App\Models\hrm\Hrm;
use App\Models\workshift\Workshift;

trait AttendanceRelationship
{
    public function employee()
    {
        return $this->belongsTo(Hrm::class, 'employee_id');
    }
    public function workshift()
    {
        return $this->belongsTo(Workshift::class, 'workshift_id');
    }
}
