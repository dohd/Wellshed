<?php

namespace App\Models\classlist\Traits;

use App\Models\invoice\Invoice;
use App\Models\items\PurchaseItem;
use App\Models\PurchaseClassBudgets\PurchaseClassBudget;

trait ClasslistRelationship
{
    public function purchase_items()
    {
        return $this->hasMany(PurchaseItem::class, 'classlist_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'classlist_id');
    }

    public function purchase_class_budget()
    {
        return $this->hasMany(PurchaseClassBudget::class, 'classlist_id');
    }
}