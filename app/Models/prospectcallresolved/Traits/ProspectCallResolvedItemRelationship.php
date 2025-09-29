<?php

namespace App\Models\prospectcallresolved\Traits;
use App\Models\prospect\Prospect;
use App\Models\prospect_question\ProspectQuestionItem;
use App\Models\prospectcallresolved\ProspectCallResolved;

/**
 * Class ProspectRelationsip* 
 **/
trait ProspectCallResolvedItemRelationship
{
    function prospect_resolved(){
        return $this->belongsTo(ProspectCallResolved::class,'prospect_call_resolved_id');
        }

    public function prospect_question(){
        return $this->belongsTo(ProspectQuestionItem::class,'question_id');
    }
}
