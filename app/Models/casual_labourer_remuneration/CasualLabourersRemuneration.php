<?php

namespace App\Models\casual_labourer_remuneration;

use App\Models\casual_labourer_remuneration\Traits\CLRRelationship;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class CasualLabourersRemuneration extends Model
{
    use ModelTrait, CLRRelationship;

    protected $table = 'casual_labourers_remunerations';

    protected $primaryKey = 'clr_number';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [];

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        // 'clr_number'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->fill([
                'tid' => self::max('tid')+1,
                'ins' => auth()->user()->ins,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            ]);
            return $model;
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->user()->id;
            return $model;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', auth()->user()->ins);
        });
    } 
}
