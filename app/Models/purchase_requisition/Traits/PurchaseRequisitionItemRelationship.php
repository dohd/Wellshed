<?php

namespace App\Models\purchase_requisition\Traits;

use App\Models\Access\User\User;
use App\Models\product\ProductVariation;
use App\Models\productvariable\Productvariable;
use App\Models\project\BudgetItem;
use App\Models\project\MileStoneItem;
use App\Models\project\Project;
use App\Models\purchase_request\PurchaseRequestItem;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Models\purchase_requisition\PurchaseRequisitionItem;

trait PurchaseRequisitionItemRelationship
{
   public function purchase_requisition()
   {
    return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id');
   }
   public function unit()
   {
    return $this->belongsTo(Productvariable::class, 'unit_id');
   }
   public function product()
   {
    return $this->belongsTo(ProductVariation::class, 'product_id');
   }
   public function milestone_item()
   {
    return $this->belongsTo(MileStoneItem::class, 'milestone_item_id');
   }
   public function budget_item()
   {
    return $this->belongsTo(BudgetItem::class, 'budget_item_id');
   }

   public function project(){
      return $this->belongsTo(Project::class, 'project_id');
  }
  public function mr_items()
   {
    return $this->belongsTo(PurchaseRequestItem::class, 'purchase_request_item_id');
   }
}
