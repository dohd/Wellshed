<?php

namespace App\Models\rfq\Traits;

use App\Models\account\Account;
use App\Models\product\ProductVariation;
use App\Models\rfq\RfQItem;
use App\Models\rfq_analysis\RfQAnalysis;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait RfQRelationship
{

    public function rfq_analysis()
    {
        return $this->hasMany(RfQAnalysis::class, 'rfq_id');
    }
}
