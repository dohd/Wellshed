<?php

namespace App\Models\stock_issue\Traits;

use App\Models\client_product\ClientProduct;
use App\Models\hrm\Hrm;
use App\Models\product\ProductVariation;
use App\Models\project\BudgetItem;
use App\Models\purchase_requisition\PurchaseRequisitionItem;
use App\Models\stock_issue\StockIssue;
use App\Models\warehouse\Warehouse;

trait StockIssueItemRelationship
{
    public function stock_issue()
    {
        return $this->belongsTo(StockIssue::class, 'stock_issue_id', 'id');
    }

    public function assignee()
    {
        return $this->belongsTo(Hrm::class, 'assignee_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productvar()
    {
        return $this->belongsTo(ProductVariation::class, 'productvar_id', 'id');
    }
    public function clientProduct()
    {
        return $this->belongsTo(ClientProduct::class, 'productvar_id', );
    }
    public function requisition_item()
    {
        return $this->belongsTo(PurchaseRequisitionItem::class, 'requisition_item_id');
    }
    public function budget_item()
    {
        return $this->belongsTo(BudgetItem::class, 'budget_item_id');
    }
}
