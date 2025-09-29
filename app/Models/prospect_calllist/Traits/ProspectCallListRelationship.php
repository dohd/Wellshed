<?php

namespace App\Models\prospect_calllist\Traits;

use App\Models\calllist\CallList;
use App\Models\prospect\Prospect;

/**
 * Class ProspectCallListRelationsip* 
 **/
trait ProspectCallListRelationship
{
    function prospect(){
    return $this->belongsTo(Prospect::class,'prospect_id');
    }
    function prospect_status(){
        return $this->belongsTo(Prospect::class,'prospect_id')->where('is_called',0);
    }

    public function call_list()
    {
        return $this->belongsTo(CallList::class, 'call_id');
    }
}
