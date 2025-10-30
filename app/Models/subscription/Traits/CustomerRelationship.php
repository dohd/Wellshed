<?php

namespace App\Models\subscription\Traits;

use App\Models\subpackage\SubPackage;

/**
 * Class CustomerRelationship
 */
trait CustomerRelationship
{
    public function package(){
        return $this->belongsTo(SubPackage::class, 'sub_package_id');
    }
}
