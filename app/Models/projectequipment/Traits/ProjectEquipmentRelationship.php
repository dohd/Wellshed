<?php

namespace App\Models\projectequipment\Traits;

use App\Models\customer\Customer;
use App\Models\equipment\Equipment;
use App\Models\branch\Branch;
use App\Models\region\Region;
use App\Models\section\Section;
use App\Models\equipmentcategory\EquipmentCategory;
//use App\Models\branch\ProductVariation;
use DB;
/**
 * Class ProductcategoryRelationship
 */
trait ProjectEquipmentRelationship
{
    public function branches()
    {
        return $this->hasMany(Self::class,'rel_id','id');
    }

  public function branch()


    {
         return $this->hasOneThrough(Branch::class,Equipment::class,'id','id','equipment_id','branch_id')->withoutGlobalScopes();

         
    
    }
    public function region()


    {
         return $this->hasOneThrough(Region::class,Equipment::class,'id','id','equipment_id','region_id')->withoutGlobalScopes();

         
    
    }

     public function section()


    {
         return $this->hasOneThrough(Section::class,Equipment::class,'id','id','equipment_id','section_id')->withoutGlobalScopes();

         
    
    }

      public function equipment()


    {
         return $this->hasOne(Equipment::class,'id','equipment_id')->withoutGlobalScopes();

         
    
    }

      public function category()


    {
         return $this->hasOneThrough(EquipmentCategory::class,Equipment::class,'id','id','equipment_id','equipment_category_id')->withoutGlobalScopes();

         
    
    }


    
}
