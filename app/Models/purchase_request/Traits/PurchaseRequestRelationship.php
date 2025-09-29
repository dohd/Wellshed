<?php

namespace App\Models\purchase_request\Traits;

use App\Models\Access\User\User;
use App\Models\project\Project;
use App\Models\project\ProjectMileStone;
use App\Models\purchase_request\PurchaseRequestItem;
use App\Models\purchase_requisition\PurchaseRequisition;

trait PurchaseRequestRelationship
{
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class,'purchase_request_id');
    }

    public function project(){
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function milestone(){
        return $this->belongsTo(ProjectMileStone::class, 'project_milestone_id');
    }

    public function purchaseRequisitions()
    {
        return $this->hasMany(PurchaseRequisition::class);
    }
}
