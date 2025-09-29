<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;

class RecipientNotification extends Model
{
    protected $table = 'recipient_notifications';

    protected $fillable = ['recipient_setting_id','reference_id','milestone_id', 'setting_type'];

    public $timestamps = false;

    /**
     * Relations
     */
   
}
