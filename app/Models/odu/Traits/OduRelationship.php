<?php

namespace App\Models\odu\Traits;

use App\Models\odu\Odu;
//use App\Models\branch\ProductVariation;
use DB;
/**
 * Class ProductcategoryRelationship
 */
trait OduRelationship
{
    public function odus()
    {
        return $this->hasMany(Self::class,'rel_id','id');
    }

public function idu()
    {
        return $this->hasOne(Odu::class,'id','rel_id');
    }

    /* public function products()
    {
        return $this->hasManyThrough(ProductVariation::class,Product::class)->select([DB::raw('qty*price as total_value'),'qty']);
    }*/
}
