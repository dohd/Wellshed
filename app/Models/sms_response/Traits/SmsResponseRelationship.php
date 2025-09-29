<?php

namespace App\Models\sms_response\Traits;

use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsCallback;

/**
 * Class SmsResponseRelationship
 */
trait SmsResponseRelationship
{
     public function sms()
     {
        return $this->belongsTo(SendSms::class, 'send_sms_id');
     }

     public function sms_callbacks(){
      return $this->hasMany(SmsCallback::class, 'reference', 'message_response_id');
     }
}
