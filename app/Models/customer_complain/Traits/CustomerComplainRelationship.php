<?php

namespace App\Models\customer_complain\Traits;

use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\hrm\HrmMeta;
use App\Models\project\Project;
use App\Models\promotions\ClientFeedback;

/**
 * Class CustomerComplainRelationship
 */
trait CustomerComplainRelationship
{
      public function solver(){
        return $this->belongsTo(Hrm::class, 'solver_id');
      }
      public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id');
      }
      public function project(){
        return $this->belongsTo(Project::class, 'project_id');
      }
      public function client_feedback()
      {
        return $this->belongsTo(ClientFeedback::class,'customer_feedback_id');
      }
}
