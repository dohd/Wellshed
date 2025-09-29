<?php

namespace App\Models\billitem\Traits;

use App\Models\account\Account;
use App\Models\project\ProjectMileStone;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InvoiceRelationship
 */
trait BillItemRelationship
{
    public function account()
    {
        return $this->belongsTo(Account::class, 'item_id')->withoutGlobalScopes();
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\customer\Customer')->withoutGlobalScopes();
    }

    public function products()
    {
        return $this->hasMany('App\Models\items\InvoiceItem', 'invoice_id')->withoutGlobalScopes();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Access\User\User');
    }
    public function term()
    {
        return $this->belongsTo('App\Models\term\Term')->withoutGlobalScopes();
    }

    public function budgetLine(): BelongsTo {

        return $this->belongsTo(ProjectMileStone::class, 'budget_line_id', 'id');
    }
}
