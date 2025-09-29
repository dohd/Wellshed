<?php

namespace App\Models\transactioncategory\Traits;

use App\Models\transaction\Transaction;

/**
 * Class TransactioncategoryRelationship
 */
trait TransactioncategoryRelationship
{
        public function amount()
        {
             return $this->hasMany(Transaction::class,'trans_category_id');
        }

        public function parent()
    {
        return $this->belongsTo('App\Models\transactioncategory\Transactioncategory', 'sub_category_id');
    }
}
