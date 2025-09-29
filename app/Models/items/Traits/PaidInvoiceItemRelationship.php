<?php

namespace App\Models\items\Traits;

use App\Models\invoice\Invoice;
use App\Models\invoice\PaidInvoice;
use App\Models\items\WithholdingItem;

trait PaidInvoiceItemRelationship
{
    public function withholding_item()
    {
        return $this->hasOne(WithholdingItem::class);
    }

    public function paid_invoice()
    {
        return $this->belongsTo(PaidInvoice::class, 'paidinvoice_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
