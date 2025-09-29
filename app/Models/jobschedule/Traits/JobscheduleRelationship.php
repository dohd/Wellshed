<?php

namespace App\Models\jobschedule\Traits;

use App\Models\project\Project;
use App\Models\region\Region;
use App\Models\customer\Customer;
use App\Models\projectequipment\Projectequipment;
use App\Models\jobschedule\JobscheduleRelation;
use DB;
/**
 * Class ProductcategoryRelationship
 */
trait JobscheduleRelationship
{
    /*public function branches()
    {
        return $this->hasMany(Self::class,'rel_id','id');
    }*/


    public function projects()
   {
        return $this->hasOne(Project::class,'id','project_id');
    }

   public function customer()
    {
        return $this->hasOne(Customer::class,'id','client_id');
    }

     public function equipments()
    {
        return $this->hasMany(Projectequipment::class,'schedule_id');
    }

     public function totalserviced()
 {
    return $this->hasMany(Projectequipment::class,'schedule_id')->whereNotNull('job_card');

 }

    /* public function regions()
    {
        return $this->hasManyThrough(ProjectRelations::class,Project::class)->where();
    }*/

     public function regions()
    {
        return $this->hasOneThrough(Region::class, ProjectRelations::class, 'region_id', 'id', 'id', 'section_id');
    }
}
