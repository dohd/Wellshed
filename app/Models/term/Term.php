<?php

namespace App\Models\term;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\term\Traits\TermAttribute;
use App\Models\term\Traits\TermRelationship;

class Term extends Model
{
    use ModelTrait, TermAttribute, TermRelationship;
       
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'terms';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = ['title', 'type', 'terms', 'ins', 'user_id'];

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

        static::creating(function ($instance) {
            $instance->ins = $instance->ins ?: auth()->user()->ins;
            $instance->user_id = $instance->user_id ?: auth()->user()->id;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
    }
}
