<?php

namespace App\Models\boq_valuation\Traits;

use App\Models\boq\BoQ;
use App\Models\boq_valuation\BoQValuationDoc;
use App\Models\boq_valuation\BoQValuationExp;
use App\Models\boq_valuation\BoQValuationItem;
use App\Models\boq_valuation\BoQValuationJC;
use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\project\Project;
use App\Models\quote\Quote;

/**
 * Class BoQValuationRelationship
 */
trait BoQValuationRelationship
{
    public function valuatedExps()
    {
        return $this->hasMany(BoQValuationExp::class, 'boq_valuation_id');
    }

    public function job_cards()
    {
        return $this->hasMany(BoQValuationJC::class, 'boq_valuation_id');
    } 
    public function boq()
    {
        return $this->belongsTo(BoQ::class, 'boq_id');
    } 

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'boq_valuation_id');
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
        return $this->hasMany(BoQValuationItem::class, 'boq_valuation_id');
    }
    public function docs()
    {
        return $this->hasMany(BoQValuationDoc::class, 'boq_valuation_id');
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
