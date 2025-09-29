<?php

namespace App\Models\invoice\Traits;

use App\Models\account\Account;
use App\Models\boq_valuation\BoQValuation;
use App\Models\promotions\CustomersPromoCodeReservation;
use App\Models\promotions\InvoicePromotionalDiscountData;
use App\Models\promotions\ReferralsPromoCodeReservation;
use App\Models\promotions\ThirdPartiesPromoCodeReservation;
use App\Models\branch\Branch;
use App\Models\creditnote\CreditNote;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\items\InvoiceItem;
use App\Models\items\InvoicePaymentItem;
use App\Models\items\TaxReportItem;
use App\Models\items\WithholdingItem;
use App\Models\job_valuation\JobValuation;
use App\Models\lead\Lead;
use App\Models\project\Project;
use App\Models\project\ProjectInvoice;
use App\Models\quote\Quote;
use App\Models\quote\QuoteInvoice;
use App\Models\stock_issue\StockIssue;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InvoiceRelationship
 */
trait InvoiceRelationship
{
    public function jobValuation()
    {
        return $this->belongsTo(JobValuation::class);
    }
    
    public function boqValuation()
    {
        return $this->belongsTo(BoQValuation::class);
    }

    public function stock_issues()
    {
        return $this->hasMany(StockIssue::class);
    }
    
    public function quotes()
    {
        return $this->hasManyThrough(Quote::class, InvoiceItem::class, 'invoice_id', 'id', 'id', 'quote_id')
            ->withoutGlobalScopes();
    }

    public function invoice_tax_reports()
    {
        return $this->hasMany(TaxReportItem::class);
    }

    public function withholding_payments()
    {
        return $this->hasMany(WithholdingItem::class)->whereNull('paid_invoice_item_id');
    }

    public function payments()
    {
        return $this->hasMany(InvoicePaymentItem::class);
    }

    public function creditnotes()
    {
        return $this->hasMany(CreditNote::class)->where('is_debit', 0);
    }

    public function debitnotes()
    {
        return $this->hasMany(CreditNote::class)->where('is_debit', 1);
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\customer\Customer')->withoutGlobalScopes();
    }

    public function products()
    {
        return $this->hasMany('App\Models\items\InvoiceItem')->withoutGlobalScopes();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Access\User\User')->withoutGlobalScopes();
    }
    public function term()
    {
        return $this->belongsTo('App\Models\term\Term')->withoutGlobalScopes();
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\transaction\Transaction', 'invoice_id');
    }

    public function attachment()
    {
        return $this->hasMany('App\Models\items\MetaEntry', 'rel_id')->where('rel_type', 1)->withoutGlobalScopes();
    }

    public function client()
    {
        return $this->hasOneThrough(Customer::class, Lead::class, 'id', 'id', 'lead_id', 'client_id')->withoutGlobalScopes();
    }

    public function branch()
    {
        return $this->hasOneThrough(Branch::class, Lead::class, 'id', 'id', 'lead_id', 'branch_id')->withoutGlobalScopes();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function ledgerAccount(): BelongsTo {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function project()
    {
        return $this->hasOneThrough(Project::class, ProjectInvoice::class, 'invoice_id', 'id', 'id', 'project_id')
            ->withoutGlobalScopes();
    }

    public function quote()
    {
        return $this->hasOneThrough(Quote::class, QuoteInvoice::class, 'invoice_id', 'id', 'id', 'quote_id')
            ->withoutGlobalScopes();
    }

    public function promoDiscounts()
    {

        return $this->hasMany(InvoicePromotionalDiscountData::class, 'invoice_id', 'id');
    }

    public function customersReservation(): BelongsTo {

        return $this->belongsTo(CustomersPromoCodeReservation::class, 'reservation', 'uuid');
    }

    public function thirdPartiesReservation(): BelongsTo {

        return $this->belongsTo(ThirdPartiesPromoCodeReservation::class, 'reservation', 'uuid');
    }

    public function referralReservation(): BelongsTo {

        return $this->belongsTo(ReferralsPromoCodeReservation::class, 'reservation', 'uuid');
    }

}
