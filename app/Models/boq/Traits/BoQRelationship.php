<?php

namespace App\Models\boq\Traits;

use App\Models\bom\BoM;
use App\Models\boq\BoQItem;
use App\Models\boq\BoQSheet;
use App\Models\boq\BoQWorkSheet;
use App\Models\boq_valuation\BoQValuation;
use App\Models\lead\Lead;

/**
 * Class boqRelationship
 */
trait BoQRelationship
{
    public function items()
    {
        return $this->hasMany(BoQItem::class, 'boq_id')->orderBy('row_index', 'ASC');
    }

    public function bom()
    {
        return $this->hasOne(BoM::class, 'boq_id', 'id');
    }

    public function sheets()
    {
        return $this->belongsToMany(BoQSheet::class, 'boq_worksheet', 'boq_id', 'boq_sheet_id');
    }

    // hasManyThrough relationship to items via sheets
    public function products()
    {
        return $this->hasManyThrough(BoQItem::class, BoQWorkSheet::class, 'boq_id', 'boq_id', 'id', 'boq_sheet_id')->withoutGlobalScopes();
    }

    public function lead(){
        return $this->belongsTo(Lead::class, 'lead_id');
    }
    public function boq_valuations()
    {
        return $this->hasMany(BoQValuation::class, 'boq_id');
    }
}
