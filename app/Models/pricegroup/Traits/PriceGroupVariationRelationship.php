<?php

namespace App\Models\pricegroup\Traits;
use App\Models\product\Product;
use App\Models\product\ProductVariation;
//use App\Models\product\ProductMeta;
//use App\Models\warehouse\Warehouse;

/**
 * Class ProductRelationship
 */
trait PriceGroupVariationRelationship
{

      public function product()
    {
        return $this->hasOne(Product::class,'id','product_id');
    }

    public function product_variation()
    {
        return $this->hasOne(ProductVariation::class,'id','product_variation_id');
    }

    /*public function warehouse()
    {
        return $this->hasOne(Warehouse::class,'id','warehouse_id');
    }

        public function product_serial()
    {
        return $this->hasMany(ProductMeta::class, 'ref_id', 'id')->where('rel_type','=',2)->withoutGlobalScopes();
    }


      

         public function category()
    {
        return $this->hasOneThrough(Productcategory::class,Product::class,'product_id','productcategory_id');
    }*/





}
