<?php

namespace App\Models\bom\Traits;

use App\Models\bom\BoMItem;
use App\Models\boq\BoQ;
use App\Models\lead\Lead;
use App\Models\quote\Quote;

/**
 * Class BoMRelationship
 */
trait BoMRelationship
{
    public function items()
    {
        return $this->hasMany(BoMItem::class, 'bom_id')->orderBy('row_index', 'ASC');
    }
    public function lead(){
        return $this->belongsTo(Lead::class, 'lead_id');
    }
    public function boq(){
        return $this->belongsTo(BoQ::class, 'boq_id');
    }
    public function quote()
    {
        return $this->hasOne(Quote::class, 'bom_id', 'id');
    }
}
