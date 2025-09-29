<?php

namespace App\Models\rfq_analysis;

use App\Models\ModelTrait;
use App\Models\rfq_analysis\Traits\RfQAnalysisAttribute;
use App\Models\rfq_analysis\Traits\RfQAnalysisRelationship;
use Illuminate\Database\Eloquent\Model;


class RfQAnalysis extends Model
{
    use ModelTrait, RfQAnalysisAttribute, RfQAnalysisRelationship;
    protected $table = 'rfq_analysis';

    protected $fillable = [];

    protected $attributes = [];


    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $guarded = [
        'id'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->user_id = auth()->user()->id;
            $instance->ins = auth()->user()->ins;
            $instance->tid = RfQAnalysis::max('tid')+1;
            return $instance;
        });


        static::addGlobalScope('ins', function ($builder) {
            $builder->where('rfq_analysis.ins', '=', auth()->user()->ins);
        });
    }


}
