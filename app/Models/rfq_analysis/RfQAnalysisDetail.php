<?php

namespace App\Models\rfq_analysis;

use Illuminate\Database\Eloquent\Model;

class RfQAnalysisDetail extends Model
{
    
    protected $table = 'rfq_analysis_details';

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

    public function rfq_analysis()
    {
        return $this->belongsTo(RfQAnalysis::class, 'rfq_analysis_id');
    }
}
