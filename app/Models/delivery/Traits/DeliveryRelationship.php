<?php

namespace App\Models\delivery\Traits;

use App\Models\delivery_frequency\DeliveryFreq;
use App\Models\delivery\DeliveryItem;
use App\Models\delivery_schedule\DeliverySchedule;
use App\Models\orders\Orders;

/**
 * Class DeliveryRelationship
 */
trait DeliveryRelationship
{
    public function items(){
        return $this->hasMany(DeliveryItem::class, 'delivery_id');
    }
    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }
    public function delivery_frequency()
    {
        return $this->belongsTo(DeliveryFreq::class, 'delivery_frequency_id');
    }
    public function delivery_schedule()
    {
        return $this->belongsTo(DeliverySchedule::class, 'delivery_schedule_id');
    }
}
