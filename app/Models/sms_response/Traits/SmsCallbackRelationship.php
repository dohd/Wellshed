<?php

namespace App\Models\sms_response\Traits;

use App\Models\sms_response\SmsResponse;

/**
 * Class SmsCallbackRelationship
 */
trait SmsCallbackRelationship
{
     public function sms_response()
     {
        return $this->belongsTo(SmsResponse::class, 'reference');
     }
}
