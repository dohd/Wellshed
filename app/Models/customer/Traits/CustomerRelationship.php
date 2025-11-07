<?php

namespace App\Models\customer\Traits;

use App\Models\account\Account;
use App\Models\boq\BoQ;
use App\Models\boq_valuation\BoQValuation;
use App\Models\promotions\CustomersPromoCodeReservation;
use App\Models\branch\Branch;
use App\Models\client_product\ClientProduct;
use App\Models\currency\Currency;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\job_valuation\JobValuation;
use App\Models\lead\Lead;
use App\Models\manualjournal\Journal;
use App\Models\orders\Orders;
use App\Models\payment_receipt\PaymentReceipt;
use App\Models\project\Project;
use App\Models\quote\Quote;
use App\Models\recentCustomer\RecentCustomerEmail;
use App\Models\recentCustomer\RecentCustomerSms;
use App\Models\subpackage\SubPackage;
use App\Models\tenant\Tenant;
use App\Models\tenant_package\TenantPackage;
use App\Models\transaction\Transaction;
use App\Models\subscription\Subscription;
use App\Models\target_zone\CustomerZoneItem;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class CustomerRelationship
 */
trait CustomerRelationship
{
    public function packages() {
        return $this->hasManyThrough(SubPackage::class, Subscription::class, 'customer_id', 'id', 'id', 'sub_package_id')
        ->withoutGlobalScopes();
    }

    public function customer_zones() {
        return $this->hasMany(CustomerZoneItem::class,'customer_id');
    }
    
    public function charges() {
        return $this->hasMany(PaymentReceipt::class)->where('entry_type', 'debit');
    }

    public function orders() {
        return $this->hasMany(Orders::class);
    }

    public function subscriptions() {
        return $this->hasMany(Subscription::class);
    }

    public function jobValuations() {
        return $this->hasMany(JobValuation::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function ar_account() {
        return $this->belongsTo(Account::class, 'ar_account_id');
    }

    public function tenant() {
        return $this->hasOneThrough(Tenant::class, TenantPackage::class, 'customer_id', 'id', 'id', 'company_id');
    }

    public function tenant_package() {
        return $this->hasOne(TenantPackage::class);
    }

    public function journal() {
        return $this->hasOne(Journal::class);
    }

    public function quotes() {
        return $this->hasMany(Quote::class);
    }

    public function products()
    {
        return $this->hasMany(ClientProduct::class);
    }

    public function client_products()
    {
        return $this->hasMany(ClientProduct::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'client_id');
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function group()
    {
        return $this->hasMany('App\Models\customergroup\CustomerGroupEntry');
    }

    public function primary_group()
    {
        return $this->hasOne('App\Models\customergroup\CustomerGroupEntry')->oldest();
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\invoice\Invoice')->orderBy('id', 'DESC');
    }

    public function deposits()
    {
        return $this->hasMany(InvoicePayment::class)->whereNull('rel_payment_id');
    }
    
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }

    public function recentCustomerEmail(): HasMany{

        return $this->hasMany(RecentCustomerEmail::class);
    }

    public function recentCustomerSms(): HasMany{

        return $this->hasMany(RecentCustomerSms::class);
    }

    public function promoCodeReservations(): HasMany {

        return $this->hasMany(CustomersPromoCodeReservation::class, 'customer_id', 'id');
    }

    public function boqs()
    {
        return $this->hasManyThrough(
            BoQ::class,      // Final model
            Lead::class,     // Intermediate model
            'client_id',               // Foreign key on Lead table
            'lead_id',                   // Foreign key on BoQ table
            'id',                        // Local key on Customer table
            'id'                         // Local key on Lead table
        )->withoutGlobalScopes();
    }

    function boqValuations() {
        return $this->hasMany(BoQValuation::class);
    }
}
