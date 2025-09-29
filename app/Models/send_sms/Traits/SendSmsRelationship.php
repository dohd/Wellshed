<?php

namespace App\Models\send_sms\Traits;

use App\Models\casual\CasualLabourer;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\sms_response\SmsCallback;
use App\Models\sms_response\SmsResponse;
use App\Models\supplier\Supplier;

/**
 * Class SendSmsRelationship
 */
trait SendSmsRelationship
{
    public function sms_response()
    {
        return $this->hasOne(SmsResponse::class);
    }

    public function employee(){
        return $this->hasMany(Hrm::class)->where('user_type', 'employee');
    }
    public function customer(){
        return $this->hasMany(Customer::class)->where('user_type', 'customer');
    }
    public function supplier(){
        return $this->hasMany(Supplier::class)->where('user_type', 'supplier');
    }
    public function labourer(){
        return $this->hasMany(CasualLabourer::class)->where('user_type', 'labourer');
    }

    public function sms_callbacks()
    {
        return $this->hasManyThrough(SmsCallback::class, SmsResponse::class, 'send_sms_id', 'id','reference','message_response_id');
    }
}
