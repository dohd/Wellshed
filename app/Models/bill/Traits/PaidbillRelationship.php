<?php

namespace App\Models\bill\Traits;

use App\Models\items\PaidbillItem;
use App\Models\supplier\Supplier;

trait PaidbillRelationship
{
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PaidbillItem::class);
    }
}
