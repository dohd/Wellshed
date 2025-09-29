<?php

namespace App\Models\projectstock\Traits;

use App\Models\items\ProjectstockItem;
use App\Models\quote\Quote;

trait ProjectStockRelationship
{
    public function items()
    {
        return $this->hasMany(ProjectstockItem::class, 'project_stock_id');
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}
