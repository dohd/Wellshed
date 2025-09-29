<?php

namespace App\Models\lead;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadSource extends Model
{

    protected $fillable = [
        'name',
        // Add other fillable fields as needed
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('lead_sources.ins', '=', auth()->user()->ins);
        });
    }

    public function leads(): HasMany{

        return $this->hasMany(Lead::class, 'lead_source_id', 'id');
    }
}
