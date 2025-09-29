<?php

namespace App\Models\project\Traits;

use App\Models\product\ProductVariation;
use App\Models\productvariable\Productvariable;
use App\Models\project\BudgetItem;
use App\Models\project\ProjectMileStone;
use App\Models\purchase_request\PurchaseRequest;
use App\Models\purchase_request\PurchaseRequestItem;

/**
 * Class ProjectRelationship
 */
trait MileStoneItemRelationship
{
    public function milestone()
    {
        return $this->belongsTo(ProjectMileStone::class ,'milestone_id');
    }
    public function unit_of_measure()
    {
        return $this->belongsTo(Productvariable::class ,'unit_id');
    }
    public function budget_item()
    {
        return $this->belongsTo(BudgetItem::class ,'budget_item_id');
    }
    public function product_variation()
    {
        return $this->belongsTo(ProductVariation::class ,'product_id');
    }
    public function material_request_item()
    {
        return $this->hasOne(PurchaseRequestItem::class, 'milestone_item_id');
    }
}
