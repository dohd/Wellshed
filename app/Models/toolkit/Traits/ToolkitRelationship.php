<?php

namespace App\Models\Toolkit\Traits;

use App\Models\toolkit\ToolkitItems;

/**
 * Class ToolkitorderRelationship
 */
trait ToolkitRelationship
{
   

    // public function items()
    // {
    //     return $this->hasMany(ToolkitItems::class, 'item_id','id');
    // }

    public function item()
    {
        return $this->hasMany(ToolkitItems::class, 'toolkit_id','id');
    }
 }
