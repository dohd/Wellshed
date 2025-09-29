<?php

namespace App\Models\casual;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\casual\Traits\CasualLabourerAttribute;
use App\Models\casual\Traits\CasualLabourerRelationship;

class CasualLabourer extends Model
{
    use ModelTrait,
        CasualLabourerAttribute,
        CasualLabourerRelationship {
        // CasualLabourerAttribute::getEditButtonAttribute insteadof ModelTrait;
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'casual_labourers';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'tid','name','id_number','job_category_id','work_type','phone_number', 'alt_phone_number',
        'email','gender','kin_name','kin_contact','kin_relationship','user_id',
        'ins','rate','home_address','home_county','casual_description'
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
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->fill([
                'user_id' => auth()->user()->id,
                'ins' => auth()->user()->ins,
                'tid' => CasualLabourer::where('ins', auth()->user()->ins)->max('tid')+1,
                'status' => 'active',
            ]);
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
    }

}
