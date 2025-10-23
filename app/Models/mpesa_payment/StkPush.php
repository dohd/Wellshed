<?php

namespace App\Models\mpesa_payment;

use App\Models\hrm\Hrm;
use Illuminate\Database\Eloquent\Model;

class StkPush extends Model
{
    protected $table = 'stk_push';

    protected $fillable = [
        'merchant_request_id',
        'checkout_request_id',
        'account_reference',
        'phone',
        'amount',
        'result_code',
        'result_desc',
        'mpesa_receipt_number',
        'paid_at',
        'raw_callback',
        'status',
    ];

    protected $casts = [
        'raw_callback' => 'array',
        'paid_at'      => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        // static::creating(function ($instance) {
        //     $instance->user_id = auth()->user()->id;
        //     $instance->ins = auth()->user()->ins;
        //     return $instance;
        // });
        // static::addGlobalScope('ins', function($builder){
        //     $builder->where('ins', '=', auth()->user()->ins);
        // });
    }
}
