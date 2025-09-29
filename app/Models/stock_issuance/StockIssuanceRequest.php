<?php

namespace App\Models\stock_issuance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockIssuanceRequest extends Model
{

    use SoftDeletes;

    protected $table = 'stock_issuance_requests';

    protected $primaryKey = 'sir_number';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'sir_number',
        'requested_by',
        'project',
        'status',
        'notes',
        'date',
    ];

    public function sirItems(): HasMany {

        return $this->hasMany(StockIssuanceRequestItems::class, 'sir_number', 'sir_number');
    }


    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('stock_issuance_requests.ins', '=', auth()->user()->ins);
        });
    }


}
