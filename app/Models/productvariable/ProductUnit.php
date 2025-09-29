<?php

namespace App\Models\productvariable;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'product_unit';

    /**
     * Dates
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];
}
