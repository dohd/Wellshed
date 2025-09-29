<?php

namespace App\Models\supplier_creditnote\Traits;

use App\Models\items\GoodsreceivenoteItem;
use App\Models\supplier_creditnote\SupplierCreditNote;

trait SupplierCreditNoteItemRelationship
{
    public function creditnote()
    {
        return $this->belongsTo(SupplierCreditNote::class, 'supplier_creditnote_id');
    }
    public function grn_item()
    {
        return $this->belongsTo(GoodsreceivenoteItem::class, 'grn_item_id');
    }
}