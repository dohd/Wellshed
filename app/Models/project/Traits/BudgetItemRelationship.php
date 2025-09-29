<?php

namespace App\Models\project\Traits;

use App\Models\product\ProductVariation;
use App\Models\project\Budget;
use App\Models\project\MileStoneItem;
use App\Models\purchase_request\PurchaseRequestItem;
use App\Models\stock_issue\StockIssueItem;

/**
 * Class ProjectRelationship
 */
trait BudgetItemRelationship
{
    public function productVariation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function milestone_items()
    {
        return $this->hasMany(MileStoneItem::class, 'budget_item_id');
    }

    public function material_requst_items()
    {
        return $this->hasMany(PurchaseRequestItem::class, 'budget_item_id');
    }
    public function stock_issue_items()
    {
        return $this->hasMany(StockIssueItem::class, 'budget_item_id');
    }

}
