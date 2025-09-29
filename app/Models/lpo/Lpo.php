<?php

namespace App\Models\lpo;

use App\Models\lpo\Traits\LpoRelationship;
use Illuminate\Database\Eloquent\Model;

class Lpo extends Model
{
    use LpoRelationship;
    
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'lpos';

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];

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
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
    }
}
