<?php

namespace App\Models\promotions;

use Illuminate\Database\Eloquent\Model;

class PromotionalCodeProductCategory extends Model
{

    protected $table = 'promotional_code_product_categories';

    protected $fillable = ['product_category_id', 'promotional_code_id'];

}
