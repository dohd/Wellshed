<?php

namespace App\Models\promotions;

use Illuminate\Database\Eloquent\Model;

class PromotionalCodeProduct extends Model
{

    protected $table = 'promotional_code_products';

    protected $fillable = ['product_variation_id', 'promotional_code_id'];
}
