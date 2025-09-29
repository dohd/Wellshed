<?php

namespace App\Models\quote;

use Illuminate\Database\Eloquent\Model;
// use App\Models\quote\Traits\QuoteFileRelationship;

class QuoteFile extends Model
{
    // use QuoteFileRelationship;


    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'quote_files';

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
        static::creating(function ($instance) {
            if(auth()->user()){
                $instance->user_id = auth()->user()->id;
                $instance->ins = auth()->user()->ins;
            }
            return $instance;
        });

        
        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}
