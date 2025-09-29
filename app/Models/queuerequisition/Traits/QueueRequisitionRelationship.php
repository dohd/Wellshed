<?php

namespace App\Models\queuerequisition\Traits;

use App\Models\hrm\Hrm;
use App\Models\hrm\HrmMeta;
use App\Models\supplier_product\SupplierProduct;

/**
 * ClassqueuerequisitionRelationship
 */
trait QueueRequisitionRelationship
{

    public function queuerequisition_supplier()
    {
        return $this->hasOne(SupplierProduct::class, 'product_code', 'product_code');
    }
     
}
