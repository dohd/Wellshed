<?php

namespace App\Models\stock_issuance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockIssuanceRequestItems extends Model
{

    use SoftDeletes;

    protected $table = 'stock_issuance_request_items';

    protected $primaryKey = 'siri_number';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'siri_number',
        'sir_number',
        'product',
        'quantity',
    ];

    public function sir(): BelongsTo {

        return $this->belongsTo(StockIssuanceRequest::class, 'sir_number', 'sir_number');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('stock_issuance_request_items.ins', '=', auth()->user()->ins);
        });
    }


}
