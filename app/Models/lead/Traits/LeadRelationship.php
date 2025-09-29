<?php

namespace App\Models\lead\Traits;

use App\Models\boq\BoQ;
use App\Models\branch\Branch;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\djc\Djc;
use App\Models\lead\AgentLead;
use App\Models\lead\LeadSource;
use App\Models\potential\Potential;
use App\Models\quote\Quote;
use App\Models\tender\Tender;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductcategoryRelationship
 */
trait LeadRelationship
{    
     public function tender()
     {
          return $this->hasOne(Tender::class);
     }

     public function currency()
     {
          return $this->belongsTo(Currency::class);
     }

     public function agentLead()
     {
          return $this->belongsTo(AgentLead::class, 'agent_lead_id');
     }

     public function djcs()
     {
          return $this->hasMany(Djc::class);
     }

     public function quotes() 
     {
          return $this->hasMany(Quote::class);
     }

     public function branch()
     {
          return $this->belongsTo(Branch::class, 'branch_id');
     }

     public function customer()
     {
          return $this->belongsTo(Customer::class, 'client_id');
     }

     public function LeadSource():BelongsTo{
         return $this->belongsTo(LeadSource::class, 'lead_source_id', 'id');
     }

     public function potential()
     {
          return $this->hasOne(Potential::class, 'lead_id')->whereNull('customer_id');
     }
     public function boq()
     {
          return $this->hasMany(BoQ::class);
     }
     
     public function boqs()
     {
          return $this->hasMany(BoQ::class);
     }
}
