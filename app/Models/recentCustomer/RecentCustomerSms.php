<?php

namespace App\Models\recentCustomer;

use App\Models\customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecentCustomerSms extends Model
{

    protected $table = 'recent_customer_sms';

    protected $fillable = [
        'customer_id',
        'phone_number',
        'content',
        'created_by',
        'prospect_name',
    ];


    public function customer(): BelongsTo{

        return $this->belongsTo(Customer::class);
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });


        static::addGlobalScope('ins', function ($builder) {

            $builder->where('ins', '=', auth()->user()->ins);
        });
    }


}
