<?php

namespace App\Models\project\Traits;

use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\event\Event;
use App\Models\event\EventRelation;
use App\Models\hrm\Hrm;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\PurchaseItem;
use App\Models\misc\Misc;
use App\Models\note\Note;
use App\Models\project\Budget;
use App\Models\project\ProjectLog;
use App\Models\project\ProjectMeta;
use App\Models\project\ProjectMileStone;
use App\Models\project\ProjectQuote;
use App\Models\project\ProjectRelations;
use App\Models\project\Task;
use App\Models\quote\Quote;
use App\Models\rjc\Rjc;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\labour_allocation\LabourAllocation;
use App\Models\project\ProjectInvoice;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\stock_issue\StockIssue;
use App\Models\transaction\Transaction;

/**
 * Class ProjectRelationship
 */
trait ProjectRelationship
{
    public function wipAccount()
    {
        return $this->belongsTo(Account::class, 'wip_account_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function deposits()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function grn_items()
    {
        return $this->hasMany(GoodsreceivenoteItem::class, 'itemproject_id');
    }

    public function purchase_items()
    {
        return $this->hasMany(PurchaseItem::class, 'itemproject_id');
    }
    
    public function misc()
    {
        return $this->belongsTo(Misc::class, 'status');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'ended_by');
    }    
    
    public function budget()
    {
        return $this->hasOneThrough(Budget::class, Quote::class, 'id', 'quote_id', 'main_quote_id', 'id')
            ->withoutGlobalScopes();
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'main_quote_id');
    }

    public function quotes()
    {
        return $this->hasManyThrough(Quote::class, ProjectQuote::class, 'project_id', 'id', 'id', 'quote_id')
            ->withoutGlobalScopes();
    }

    public function rjc()
    {
        return $this->hasOne(Rjc::class);
    }

    public function rjcs()
    {
        return $this->hasMany(Rjc::class)->withoutGlobalScopes;
    }

    public function tags()
    {
        return $this->hasManyThrough(Misc::class, ProjectRelations::class, 'project_id', 'id', 'id', 'misc_id');
    }

    public function task_status()
    {
        return $this->hasOne(Misc::class, 'id', 'status')->where('section', '=', 2)->withoutGlobalScopes();
    }

    public function users()
    {
        return $this->hasManyThrough(Hrm::class, ProjectRelations::class, 'project_id', 'id', 'id', 'user_id')->whereNull('task_id')->whereNull('milestone_id');
    }

    public function creator()
    {
        return $this->hasOneThrough(Hrm::class, ProjectRelations::class, 'project_id', 'id', 'id', 'user_id');
    }
    
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function tasks()
    {
        return $this->hasOneThrough(Task::class, ProjectRelations::class, 'project_id', 'id', 'id', 'task_id');
    }

    public function tasks_status()
    {
        return $this->hasOneThrough(Task::class, ProjectRelations::class, 'project_id', 'id', 'id', 'task_id');
    }

    public function milestones()
    {
        return $this->hasMany(ProjectMileStone::class, 'project_id', 'id')->orderBy('due_date', 'desc')->withoutGlobalScopes();;
    }

    public function history()
    {
        return $this->hasMany(ProjectLog::class, 'project_id', 'id')->orderBy('id', 'desc');
    }

    public function attachment()
    {
        return $this->hasMany(ProjectMeta::class, 'project_id', 'id')->where('meta_key', '=', 1)->orderBy('id', 'desc')->withoutGlobalScopes();
    }

    public function notes()
    {
        return $this->hasManyThrough(Note::class, ProjectRelations::class, 'project_id', 'id', 'id', 'note_id');
    }

    public function events()
    {
        return $this->hasOneThrough(Event::class, EventRelation::class, 'r_id', 'id', 'id', 'event_id')->where('related', '=', 1)->withoutGlobalScopes();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id')->withoutGlobalScopes(['currency_id']);
    }

    public function customer_project()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    
    public function labour_allocations()
    {
        return $this->hasmany(LabourAllocation::class, 'project_id');
    }

    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, ProjectInvoice::class, 'project_id', 'id', 'id', 'invoice_id')
            ->withoutGlobalScopes();
    }

    public function project_notes()
    {
        return $this->hasMany(Note::class);
    }
    public function lpo()
    {
        return $this->hasMany(Purchaseorder::class);
    }

    public function stock_issuances() {

        return $this->hasMany(StockIssue::class, 'quote_id', 'main_quote_id');
    }
    public function stock_issues() {

        return $this->hasMany(StockIssue::class, 'project_id');
    }

}
