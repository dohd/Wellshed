<?php

namespace App\Models\purchase_requisition\Traits;

use App\Models\Access\User\User;
use App\Models\hrm\Hrm;
use App\Models\project\Project;
use App\Models\project\ProjectMileStone;
use App\Models\purchase_request\PurchaseRequest;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Models\purchase_requisition\PurchaseRequisitionItem;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\stock_issue\StockIssue;

trait PurchaseRequisitionRelationship
{
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
    public function items()
    {
        return $this->hasMany(PurchaseRequisitionItem::class,'purchase_requisition_id');
    }

    public function project(){
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function milestone(){
        return $this->belongsTo(ProjectMileStone::class, 'project_milestone_id');
    }
    public function purchase_request(){
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }
    public function approved(){
        return $this->belongsTo(Hrm::class, 'approved_by');
    }
    public function purchaseOrder()
    {
        return $this->hasOne(Purchaseorder::class);
    }

    public function stockIssue()
    {
        return $this->hasOne(StockIssue::class);
    }
    public function pr_parent()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_parent_id');
    }
    public function pr_child()
    {
        return $this->hasOne(PurchaseRequisition::class, 'pr_parent_id');
    }
}
