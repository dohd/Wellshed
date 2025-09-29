<?php

namespace App\Models\job_valuation\Traits;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\job_valuation\JobValuationDoc;
use App\Models\job_valuation\JobValuationExp;
use App\Models\job_valuation\JobValuationItem;
use App\Models\job_valuation\JobValuationJC;
use App\Models\quote\Quote;

trait JobValuationRelationship
{   
    public function valuatedExps()
    {
        return $this->hasMany(JobValuationExp::class, 'job_valuation_id');
    }

    public function job_cards()
    {
        return $this->hasMany(JobValuationJC::class, 'job_valuation_id');
    } 

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    } 

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    } 

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(JobValuationItem::class);
    }
    public function docs()
    {
        return $this->hasMany(JobValuationDoc::class, 'job_valuation_id');
    }
}
