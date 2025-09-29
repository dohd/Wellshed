<?php

namespace App\Models\workshift\Traits;

use App\Models\workshift\WorkshiftItems;

/**
 * Class workshiftorderRelationship
 */
trait WorkshiftRelationship
{
   

    // public function items()
    // {
    //     return $this->hasMany(workshiftItems::class, 'item_id','id');
    // }

    public function item()
    {
        return $this->hasOne(WorkshiftItems::class, 'workshift_id','id');
    }
 }
