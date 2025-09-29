<?php

namespace App\Models\ticket_category;

use App\Models\ModelTrait;
use App\Models\ticket_category\Traits\TicketCategoryAttribute;
use App\Models\ticket_category\Traits\TicketCategoryRelationship;
use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    use ModelTrait, TicketCategoryAttribute, TicketCategoryRelationship;

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'ticket_categories';

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

    /**
     * model life cycle event listeners
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->user_id = auth()->user()->id;
            $instance->ins = auth()->user()->ins;
            return $instance;
        });
    }
}
