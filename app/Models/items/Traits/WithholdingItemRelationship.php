<?php

namespace App\Models\items\Traits;

use App\Models\invoice\Invoice;
use App\Models\withholding\Withholding;

/**
 * Class InvoiceItemRelationship
 */
trait WithholdingItemRelationship
{
    public function withholding()
    {
        return $this->belongsTo(Withholding::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
