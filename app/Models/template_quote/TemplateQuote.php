<?php

namespace App\Models\template_quote;

use App\Models\ModelTrait;
use App\Models\template_quote\Traits\TemplateQuoteAttribute;
use App\Models\template_quote\Traits\TemplateQuoteRelationship;
use Illuminate\Database\Eloquent\Model;

class TemplateQuote extends Model
{
    use ModelTrait, TemplateQuoteAttribute, TemplateQuoteRelationship;

/**
 * NOTE : If you want to implement Soft Deletes in this model,
 * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
 */

/**
 * The database table used by the model.
 * @var string
 */
protected $table = 'template_quotes';

/**
 * Mass Assignable fields of model
 * @var array
 */
protected $fillable = [];

/**
 * Default values for model fields
 * @var array
 */
protected $attributes = [];

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

/**
 * Constructor of Model
 * @param array $attributes
 */
public function __construct(array $attributes = [])
{
    parent::__construct($attributes);
}
protected static function boot()
{
    parent::boot();
    static::addGlobalScope('ins', function ($builder) {
        $builder->where('ins', '=', auth()->user()->ins);
    });
}
}
