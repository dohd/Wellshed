<?php

namespace App\Models\creditnote\Traits;

use App\Models\creditnote\CreditNote;
use App\Models\items\InvoiceItem;
use App\Models\product\ProductVariation;

trait CreditNoteItemRelationship
{
    public function creditnote()
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function invoice_item()
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }

    public function productvar()
    {
        return $this->belongsTo(ProductVariation::class, 'productvar_id');
    }
}