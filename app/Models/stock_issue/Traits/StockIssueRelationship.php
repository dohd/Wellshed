<?php

namespace App\Models\stock_issue\Traits;

use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\invoice\Invoice;
use App\Models\part\Part;
use App\Models\project\Project;
use App\Models\purchase_request\PurchaseRequest;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Models\quote\Quote;
use App\Models\stock_issue\StockIssue;
use App\Models\stock_issue\StockIssueItem;
use App\Models\transaction\Transaction;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait StockIssueRelationship
{    
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'stock_issue_id');
    }

    public function employee()
    {
        return $this->belongsTo(Hrm::class, 'employee_id');
    }
    public function user()
    {
        return $this->belongsTo(Hrm::class, 'user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(StockIssueItem::class, 'stock_issue_id', 'id');
    }

    public function issued_products()
    {
        return $this->hasMany(StockIssueItem::class, 'stock_issue_id', 'id');
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id', 'id');
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    public function approver(): BelongsTo{

        return $this->belongsTo(Hrm::class, 'approved_by', 'id');
    }

    public function purchase_request()
    {
        return $this->belongsTo(PurchaseRequest::class, 'requisition_id');
    }
    public function purchase_requisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id');
    }

    public function related_project() {

        return $this->belongsTo(Project::class, 'quote_id', 'main_quote_id');
    }

    public function finished_good()
    {
        return $this->belongsTo(Part::class, 'finished_good_id');
    }
}
