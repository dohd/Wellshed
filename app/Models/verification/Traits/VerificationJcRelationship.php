<?php

namespace App\Models\verification\Traits;

use App\Models\equipment\Equipment;

trait VerificationJcRelationship
{
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
