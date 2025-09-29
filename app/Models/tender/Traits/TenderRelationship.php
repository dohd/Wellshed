<?php

namespace App\Models\tender\Traits;

use App\Models\customer\Customer;
use App\Models\lead\Lead;
use App\Models\tender\TenderFollowup;

/**
 * Class TenderRelationship
 */
trait TenderRelationship
{
     public function follow_ups()
     {
        return $this->hasMany(TenderFollowup::class , 'tender_id');
     }

     public function lead()
     {
        return $this->belongsTo(Lead::class, 'lead_id');
     }

     public function client()
    {
        return $this->hasOneThrough(Customer::class, Lead::class, 'id', 'id', 'lead_id', 'client_id')->withoutGlobalScopes();
    }
}
