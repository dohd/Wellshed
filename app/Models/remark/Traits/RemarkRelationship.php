<?php

namespace App\Models\remark\Traits;

use App\Models\prospect\Prospect;

/**
 * Class ProductcategoryRelationship
 */
trait RemarkRelationship
{

     public function prospect()
     {
          return $this->belongsTo(Prospect::class,'prospect_id');
     }
}
