<?php

namespace App\Models\items\Traits;

use App\Models\product\ProductVariation;
use App\Models\project\BudgetItem;
use App\Models\projectstock\Projectstock;
use App\Models\warehouse\Warehouse;

trait ProjectstockItemRelationship
{
    public function project_stock()
    {
        return $this->belongsTo(Projectstock::class, 'project_stock_id');
    }

    public function product_variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }

    public function budget_item()
    {
        return $this->belongsTo(BudgetItem::class, 'budget_item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
