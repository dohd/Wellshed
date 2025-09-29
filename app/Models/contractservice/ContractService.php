<?php

namespace App\Models\contractservice;

use App\Models\contractservice\Traits\ContractServiceAtrribute;
use App\Models\contractservice\Traits\ContractServiceRelationship;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class ContractService extends Model
{
    use  ModelTrait, ContractServiceAtrribute, ContractServiceRelationship;
    
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'contract_services';

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
