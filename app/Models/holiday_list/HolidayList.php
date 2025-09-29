<?php

namespace App\Models\holiday_list;

use App\Models\holiday_list\Traits\HolidayListAttribute;
use App\Models\holiday_list\Traits\HolidayListRelationship;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class HolidayList extends Model
{
    use ModelTrait, HolidayListAttribute, HolidayListRelationship;

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'leave_holidays';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'title', 'note', 'date', 'is_recurrent', 'user_id', 'ins'
    ];

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

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', '=', auth()->user()->ins);
        });
    }
}
