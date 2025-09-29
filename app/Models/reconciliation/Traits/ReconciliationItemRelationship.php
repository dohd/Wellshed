<?php

namespace App\Models\reconciliation\Traits;

use App\Models\banktransfer\Banktransfer;
use App\Models\billpayment\Billpayment;
use App\Models\charge\Charge;
use App\Models\creditnote\CreditNote;
use App\Models\invoice\PaidInvoice;
use App\Models\items\JournalItem;
use App\Models\manualjournal\Journal;
use App\Models\reconciliation\Reconciliation;

trait ReconciliationItemRelationship
{
    public function reconciliation()
    {
        return $this->belongsTo(Reconciliation::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'man_journal_id');
    }

    public function journal_item()
    {
        return $this->belongsTo(JournalItem::class, 'journal_item_id');
    }

    public function payment()
    {
        return $this->belongsTo(Billpayment::class, 'payment_id');
    }

    public function deposit()
    {
        return $this->belongsTo(PaidInvoice::class, 'deposit_id');
    }

    public function bank_transfer()
    {
        return $this->belongsTo(Banktransfer::class);
    }

    public function charge()
    {
        return $this->belongsTo(Charge::class);
    }

    public function creditnote()
    {
        return $this->belongsTo(CreditNote::class, 'credinote_id');
    }
}