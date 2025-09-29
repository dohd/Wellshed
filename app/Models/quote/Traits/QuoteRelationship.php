<?php

namespace App\Models\quote\Traits;

use App\Models\Access\User\User;
use App\Models\bank\Bank;
use App\Models\bom\BoM;
use App\Models\customer\Customer;
use App\Models\branch\Branch;
use App\Models\currency\Currency;
use App\Models\invoice\Invoice;
use App\Models\items\InvoiceItem;
use App\Models\items\MetaEntry;
use App\Models\items\QuoteItem;
use App\Models\items\VerifiedItem;
use App\Models\job_valuation\JobValuation;
use App\Models\lead\Lead;
use App\Models\lpo\Lpo;
use App\Models\product\ProductVariation;
use App\Models\project\Budget;
use App\Models\project\BudgetSkillset;
use App\Models\project\Project;
use App\Models\project\ProjectQuote;
use App\Models\projectstock\Projectstock;
use App\Models\stock_issue\StockIssue;
use App\Models\term\Term;
use App\Models\verifiedjcs\VerifiedJc;
use App\Models\quote\EquipmentQuote;
use App\Models\quote\QuoteFile;
use App\Models\quote\QuoteInvoice;
use App\Models\sale_return\SaleReturn;
use Illuminate\Database\Eloquent\Relations\HasMany;


trait QuoteRelationship
{
    public function saleReturn()
    {
        return $this->hasOne(SaleReturn::class);
    }

    public function job_valuations()
    {
        return $this->hasMany(JobValuation::class, 'quote_id');
    }

    public function stock_issues()
    {
        return $this->hasMany(StockIssue::class);
    }

    public function project()
    {
        return $this->hasOneThrough(Project::class, ProjectQuote::class, 'quote_id', 'id', 'id', 'project_id')
            ->withoutGlobalScopes();
    }

    public function projectstock()
    {
        return $this->hasMany(Projectstock::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_quotes', 'quote_id', 'project_id');
    }

    public function skill_items()
    {
        return $this->hasMany(BudgetSkillset::class);
    }

    public function invoice_product()
    {
        return $this->hasOne('App\Models\items\InvoiceItem')->withoutGlobalScopes();
    }
    public function invoice_items()
    {
        return $this->hasMany('App\Models\items\InvoiceItem')->withoutGlobalScopes();
    }

    public function invoice()
    {
        return $this->hasOneThrough(Invoice::class, InvoiceItem::class, 'quote_id', 'id', 'id', 'invoice_id')
            ->withoutGlobalScopes();
    }

    public function budget()
    {
        return $this->hasOne(Budget::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function lpo()
    {
        return $this->belongsTo(Lpo::class);
    }

    public function project_quote()
    {
        return $this->hasOne(ProjectQuote::class);
    }

    public function verified_jcs()
    {
        return $this->hasMany(VerifiedJc::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function verified_products()
    {
        return $this->hasMany(VerifiedItem::class)->orderBy('row_index', 'ASC');
    }

    public function products()
    {
        return $this->hasMany(QuoteItem::class)->orderBy('row_index', 'ASC');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withoutGlobalScopes();
    }

    public function term()
    {
        return $this->belongsTo(Term::class)->withoutGlobalScopes();
    }

    public function attachment()
    {
        return $this->hasMany(MetaEntry::class, 'rel_id')->where('rel_type', '=', 4)->withoutGlobalScopes();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function quote_client()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function client()
    {
        return $this->hasOneThrough(Customer::class, Lead::class, 'id', 'id', 'lead_id', 'client_id')->withoutGlobalScopes();
    }

    public function branch()
    {
        return $this->hasOneThrough(Branch::class, Lead::class, 'id', 'id', 'lead_id', 'branch_id')->withoutGlobalScopes();
    }

    public function lead()
    {
        return $this->hasOne(Lead::class, 'id', 'lead_id')->withoutGlobalScopes();
    }
    
    public function equipments()
    {
        return $this->hasMany(EquipmentQuote::class, 'quote_id')->withoutGlobalScopes();
    }
    public function preparedBy(){
        return $this->belongsTo(User::class, 'prepared_by_user');
    }

    public function stockIssues(): HasMany {

        return $this->hasMany(StockIssue::class, 'quote_id', 'id');
    }
    public function invoice_quote()
    {
        return $this->hasOneThrough(Invoice::class, QuoteInvoice::class, 'quote_id', 'id', 'id', 'invoice_id');
    }
    public function productvar(){
        return $this->belongsTo(ProductVariation::class, 'productvar_id');
    }

    public function quote_files()
    {
        return $this->hasMany(QuoteFile::class);
    }

    public function bom()
    {
        return $this->belongsTo(BoM::class, 'bom_id');
    }
}