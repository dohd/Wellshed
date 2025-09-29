<?php

namespace App\Models\section\Traits;

use App\Models\customer\Customer;
//use App\Models\branch\ProductVariation;
use DB;
/**
 * Class ProductcategoryRelationship
 */
trait SectionRelationship
{
    public function branches()
    {
        return $this->hasMany(Self::class,'rel_id','id');
    }

public function customer()
    {
        return $this->hasOne(Customer::class,'id','rel_id');
    }

    /* public function products()
    {
        return $this->hasManyThrough(ProductVariation::class,Product::class)->select([DB::raw('qty*price as total_value'),'qty']);
    }*/
}
