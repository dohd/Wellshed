<?php

namespace App\Models\productcategory\Traits;

use App\Models\product\Product;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;

/**
 * Class ProductcategoryRelationship
 */
trait ProductcategoryRelationship
{
    public function subcategories()
    {
        return $this->hasMany(Self::class, 'rel_id', 'id');
    }

    public function products()
    {
        return $this->hasManyThrough(ProductVariation::class, Product::class, 'productcategory_id', 'parent_id')->withoutGlobalScopes();
    }

    public function product_variations()
    {
        return $this->hasManyThrough(ProductVariation::class, Product::class, 'productcategory_id', 'parent_id')->withoutGlobalScopes();
    }

    public function parent_category()
    {
        return $this->belongsTo(Productcategory::class, 'rel_id');
    }
    public function child()
    {
        return $this->belongsTo(Productcategory::class, 'child_id');
    }

    //
    public function parent()
    {
        return $this->belongsTo(Productcategory::class, 'rel_id');
    }

    public function grandChildren()
    {
        return $this->belongsTo(Productcategory::class, 'child_id');
    }
}
