<?php

namespace App\Models\purchase_request\Traits;

use App\Models\Access\User\User;
use App\Models\product\ProductVariation;
use App\Models\productvariable\Productvariable;
use App\Models\project\BudgetItem;
use App\Models\project\MileStoneItem;
use App\Models\purchase_request\PurchaseRequest;

trait PurchaseRequestItemRelationship
{
   public function purchase_request()
   {
    return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
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
}
