<?php

namespace App\Models\projectstocktransfer\Traits;

/**
 * Class PurchaseorderRelationship
 */
trait ProjectstocktransferRelationship

{
           public function customer()
    {
        return $this->belongsTo('App\Models\customer\Customer','payer_id','id')->withoutGlobalScopes();
    }

          public function branch()
    {
        return $this->belongsTo('App\Models\branch\Branch','branch_id','id')->withoutGlobalScopes();
    }
       public function project()
    {
        return $this->belongsTo('App\Models\project\Project','project_id','id')->withoutGlobalScopes();
    }


       public function supplier()
    {
        return $this->belongsTo('App\Models\supplier\Supplier')->withoutGlobalScopes();
    }



     public function products()
    {
        return $this->hasMany('App\Models\items\PurchaseItem','bill_id')->withoutGlobalScopes();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Access\User\User')->withoutGlobalScopes();
    }
    public function term()
    {
        return $this->belongsTo('App\Models\term\Term')->withoutGlobalScopes();
    }

    public function attachment()
    {
        return $this->hasMany('App\Models\items\MetaEntry','rel_id')->where('rel_type','=',9)->withoutGlobalScopes();
    }

}
