<?php

namespace App\Models\delivery_schedule\Traits;

use App\Models\customer\Customer;
use App\Models\delivery\Delivery;
use App\Models\delivery_frequency\DeliveryFreq;
use App\Models\delivery_schedule\DeliveryScheduleItem;
use App\Models\hrm\Hrm;
use App\Models\orders\Orders;
use App\Models\target_zone\TargetZoneItem;

/**
 * Class DeliveryScheduleRelationship
 */
trait DeliveryScheduleRelationship
{
    public function items(){
        return $this->hasMany(DeliveryScheduleItem::class, 'delivery_schedule_id');
    }
    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }
    public function delivery_frequency()
    {
        return $this->belongsTo(DeliveryFreq::class, 'delivery_frequency_id');
    }
    public function store_manager()
    {
        return $this->belongsTo(Hrm::class, 'dispatched_by');
    }
    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_schedule_id');
    }
    public function location()
    {
        return $this->belongsTo(TargetZoneItem::class, 'location_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
