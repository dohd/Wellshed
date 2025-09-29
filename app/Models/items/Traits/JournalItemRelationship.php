<?php

namespace App\Models\items\Traits;

use App\Models\account\Account;
use App\Models\customer\Customer;
use App\Models\manualjournal\Journal;
use App\Models\project\Project;
use App\Models\reconciliation\ReconciliationItem;
use App\Models\supplier\Supplier;

trait JournalItemRelationship
{
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function reconciliation_items()
    {
        return $this->hasMany(ReconciliationItem::class, 'journal_item_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
