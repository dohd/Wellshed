<?php

namespace App\Models\hrm\Traits;

use App\Models\department\Department;
use App\Models\hrm\HrmMeta;
use App\Models\jobtitle\JobTitle;
use App\Models\workshift\Workshift;

/**
 * Class HrmRelationship
 */
trait HrmMetaRelationship
{

   
    public function jobtitle()
    {
        return $this->hasOne(JobTitle::class, 'id','position');
    }
    public function department()
    {
        return $this->hasOne(Department::class, 'id','department_id');
    }


    public function employeeJobTitle()
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id','id');
    }

    public function workshift()
    {
        return $this->belongsTo(Workshift::class, 'workshift_id', 'id');
    }

}
