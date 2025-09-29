<?php

namespace App\Models\toolkit\Traits;

use App\Models\product\ProductVariation;

/**
 * Class ToolkitorderRelationship
 */
trait ToolkitItemsRelationship
{
   

    // public function items()
    // {
    //     return $this->hasMany(ToolkitItems::class, 'item_id','id');
    // }

    public function equipment_toolkit()
    {
        return $this->hasOne(ProductVariation::class, 'id','item_id')->withoutGlobalScopes();
    }
 }
