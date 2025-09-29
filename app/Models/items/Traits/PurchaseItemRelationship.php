<?php

namespace App\Models\items\Traits;

use App\Models\account\Account;
use App\Models\assetequipment\Assetequipment;
use App\Models\project\Project;
use App\Models\project\ProjectMileStone;
use App\Models\purchase\Purchase;
use App\Models\PurchaseClassBudgets\PurchaseClassBudget;
use App\Models\transaction\Transaction;
use App\Models\warehouse\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


trait PurchaseItemRelationship
{
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'purchase_item_id');
    }

    // budget-line or milestone
    public function budgetLine()
    {
        return $this->belongsTo(ProjectMileStone::class, 'budget_line_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'bill_id');
    }

    public function asset()
    {
        return $this->belongsTo(Assetequipment::class, 'item_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'item_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'itemproject_id');
    }

    public function productvariation() {
        return $this->belongsTo('App\Models\product\ProductVariation', 'item_id')->withoutGlobalScopes();
    }

    public function product()
    {
        return $this->belongsTo('App\Models\product\ProductVariation', 'item_id')->withoutGlobalScopes();
    }

    public function variation()
    {
        return $this->belongsTo('App\Models\product\ProductVariation', 'item_id')->withoutGlobalScopes();
    }

    public function purchaseClassBudget(): BelongsTo
    {
        return $this->belongsTo(PurchaseClassBudget::class, 'purchase_class_budget', 'id');
    }

}
