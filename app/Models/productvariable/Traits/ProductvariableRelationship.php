<?php

namespace App\Models\productvariable\Traits;

use App\Models\product\Product;

/**
 * Class ProductvariableRelationship
 */
trait ProductvariableRelationship
{
     public function variation() {

        return $this->belongsTo('App\Models\productvariable\Productvariable','id','sub');
    }

    public function products() {
        return $this->hasMany(Product::class, 'unit_id');
    }

}
