<?php

namespace App\Models\prospectcallresolved\Traits;
use App\Models\prospect\Prospect;
use App\Models\prospectcallresolved\ProspectCallResolvedItem;

/**
 * Class ProspectRelationsip* 
 **/
trait ProspectCallResolvedRelationship
{
    function prospect(){
        return $this->belongsTo(Prospect::class,'prospect_id');
        }
    
    public function items()
    {
        return $this->hasMany(ProspectCallResolvedItem::class);
    }
}
