<?php

namespace App\Models\tenant;

use App\Models\ModelTrait;
use App\Models\tenant\Traits\TenantAttribute;
use App\Models\tenant\Traits\TenantRelationship;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use ModelTrait, TenantAttribute, TenantRelationship;

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'companies';

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
            $instance->fill([
                'tid' => Tenant::max('tid')+1,
                'main_date_format' => 'd-m-Y',
                'user_date_format' => 'DD-MM-YYYY',
                'zone' => 'Africa/Nairobi',
                'lang' => 'english',
                'valid' => 1,
                'tax' => 0,
                'currency' => 'E',
                'currency_format' => 0,
            ]);
            return $instance;
        });
    }
}
