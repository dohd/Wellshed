<?php

namespace App\Models\purchaseorder\Traits;

use App\Models\currency\Currency;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\items\PurchaseorderItem;
use App\Models\project\Project;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Models\PurchaseClassBudgets\PurchaseClassBudget;
use App\Models\purchaseorder\PurchaseorderReview;
use App\Models\rfq\RfQ;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PurchaseorderRelationship
 */
trait PurchaseorderRelationship
{
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function goods()
    {
        return $this->hasMany(PurchaseorderItem::class, 'purchaseorder_id');
    }

    public function grn_items()
    {
        return $this->hasManyThrough(GoodsreceivenoteItem::class, Goodsreceivenote::class, 'purchaseorder_id', 'goods_receive_note_id')->withoutGlobalScopes();
    }

    public function grns()
    {
        return $this->hasMany(Goodsreceivenote::class, 'purchaseorder_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseorderItem::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\supplier\Supplier', 'supplier_id')->withoutGlobalScopes();
    }

    public function supplier()
    {
        return $this->belongsTo('App\Models\supplier\Supplier')->withoutGlobalScopes();
    }

    public function products()
    {
        return $this->hasMany('App\Models\items\PurchaseorderItem')->withoutGlobalScopes();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Access\User\User')->withoutGlobalScopes();
    }

    public function term()
    {
        return $this->belongsTo('App\Models\term\Term')->withoutGlobalScopes();
    }

    public function attachment()
    {
        return $this->hasMany('App\Models\items\MetaEntry', 'rel_id')->where('rel_type', '=', 9)->withoutGlobalScopes();
    }

    public function purchaseClassBudget(): BelongsTo
    {

        return $this->belongsTo(PurchaseClassBudget::class, 'purchase_class_budget', 'id');
    }

    public function rfq()
    {
        return $this->belongsTo(RfQ::class, 'rfq_id', 'id');
    }
    public function purchase_requisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id', 'id');
    }

    public function lpo_review()
    {
        return $this->hasMany(PurchaseorderReview::class, 'purchase_order_id');
    }

}
