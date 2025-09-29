<?php

namespace App\Models\items\Traits;

use App\Models\rjc\Rjc;

trait RjcItemRelationship
{

    public function rjc()
    {
        return $this->belongsTo(Rjc::class);
    }
}
