<?php

namespace App\Models\template_quote;

use App\Models\template_quote\Traits\TemplateQuoteItemRelationship;
use App\Models\template_quote\Traits\TemplateQuoteItemAttribute;
use Illuminate\Database\Eloquent\Model;

class TemplateQuoteItem extends Model
{
    use TemplateQuoteItemAttribute, TemplateQuoteItemRelationship;

    protected $table = 'template_quote_items';

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
