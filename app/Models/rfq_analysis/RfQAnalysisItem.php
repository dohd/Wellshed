<?php

namespace App\Models\rfq_analysis;

use App\Models\rfq_analysis\Traits\RfQAnalysisItemRelationship;
use Illuminate\Database\Eloquent\Model;

class RfQAnalysisItem extends Model
{
    use RfQAnalysisItemRelationship;
    
    protected $table = 'rfq_analysis_items';

    protected $fillable = [
    ];

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
            return $instance;
        });
    }
}
