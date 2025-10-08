<?php

namespace App\Models\orders\Traits;

use App\Models\customer\Customer;
use App\Models\delivery_frequency\DeliveryFreq;
use App\Models\delivery_schedule\DeliverySchedule;
use App\Models\orders\OrdersItem;

/**
 * Class OrdersRelationship
 */
trait OrdersRelationship
{
    public function items()
    {
        return $this->hasMany(OrdersItem::class,'order_id');
    }

    public function deliver_days()
    {
        return $this->hasMany(DeliveryFreq::class, 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }
    public function schedules()
    {
        return $this->hasMany(DeliverySchedule::class, 'order_id');
    }
}
