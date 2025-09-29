<?php

namespace App\Models\stock_issuance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockIssuanceApproval extends Model
{

    use SoftDeletes;

    protected $table = 'stock_issuance_approvals';

    protected $primaryKey = 'sia_number';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'sia_number',
        'sir_number',
        'approved_by',
        'date',
    ];


    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('stock_issuance_approvals.ins', '=', auth()->user()->ins);
        });
    }


}
