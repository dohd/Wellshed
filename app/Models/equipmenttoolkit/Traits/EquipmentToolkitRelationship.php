<?php

namespace App\Models\equipmenttoolkit\Traits;

//use App\Models\Toolkit\SurchargeItems;

/**
 * Class surchargeorderRelationship
 */
trait EquipmentToolkitRelationship
{
   

    // public function items()
    // {
    //     return $this->hasMany(ToolkitItems::class, 'item_id','id');
    // }

    public function item()
    {
        return $this->hasMany(Toolkit::class, 'toolkit_id','id');
    }
 }
