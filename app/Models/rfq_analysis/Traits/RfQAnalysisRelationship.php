<?php

namespace App\Models\rfq_analysis\Traits;

use App\Models\rfq\RfQ;
use App\Models\rfq_analysis\RfQAnalysisDetail;
use App\Models\rfq_analysis\RfQAnalysisItem;
use App\Models\rfq_analysis\RfQAnalysisSupplierItem;
use App\Models\supplier\Supplier;

trait RfQAnalysisRelationship
{

    public function rfq(){
        return $this->belongsTo(RfQ::class, 'rfq_id');
    }
    public function items()
    {
        return $this->hasMany(RfQAnalysisItem::class, 'rfq_analysis_id');
    }
    public function supplier_items()
    {
        return $this->hasMany(RfQAnalysisSupplierItem::class, 'rfq_analysis_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function details()
    {
        return $this->hasMany(RfQAnalysisDetail::class, 'rfq_analysis_id');
    }
}
