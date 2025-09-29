<?php

namespace App\Models\items\Traits;

use App\Models\account\Account;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\product\ProductVariation;
use App\Models\project\Project;
use App\Models\PurchaseClassBudgets\PurchaseClassBudget;
use App\Models\purchaseorder\Purchaseorder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait PurchaseorderItemRelationship
{
    public function purchaseorder()
    {
        return $this->belongsTo(Purchaseorder::class, 'purchaseorder_id');
    }

    public function grn()
    {
        return $this->hasOneThrough(Goodsreceivenote::class, GoodsreceivenoteItem::class, 'purchaseorder_item_id', 'id', 'id', 'goods_receive_note_id');
    }

    public function grn_items()
    {
        return $this->hasMany(GoodsreceivenoteItem::class, 'purchaseorder_item_id');
    }

    public function asset()
    {
        // return $this->belongsTo(Assetequipment::class, 'item_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'item_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'itemproject_id');
    }

    public function productvariation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\product\ProductVariation', 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo('App\Models\product\ProductVariation', 'product_id')->withoutGlobalScopes();
    }

    public function purchaseClassBudget(): BelongsTo
    {

        return $this->belongsTo(PurchaseClassBudget::class, 'purchase_class_budget', 'id');
    }

}
