<?php

namespace App\Models\Company;

use App\Models\lead\OmniUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{

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
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];

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
            $instance->agent_key = Str::uuid()->toString();
            return $instance;
        });
        static::updating(function ($instance) {
            if(empty($instance->agent_key)) $instance->agent_key = Str::uuid()->toString();
            return $instance;
        });
    }

    // relations
    public function omniUser()
    {
        return $this->hasOne(OmniUser::class, 'ins');
    }
}
