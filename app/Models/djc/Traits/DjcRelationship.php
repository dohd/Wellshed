<?php

namespace App\Models\djc\Traits;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\lead\Lead;

/**
 * Class ProductcategoryRelationship
 */
trait DjcRelationship
{
     public function lead()
     {
          return $this->belongsTo(Lead::class);
     }

     public function client()
     {
          return $this->belongsTo(Customer::class, 'client_id');
     }

     public function branch()
     {
          return $this->belongsTo(Branch::class, 'branch_id');
     }

     public function items()
     {
          return $this->hasMany('App\Models\items\DjcItem')->withoutGlobalScopes();
     }
}
