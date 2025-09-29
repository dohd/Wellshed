<?php

namespace App\Models\equipment\Traits;

use App\Models\contract\Contract;
use App\Models\contract_equipment\ContractEquipment;
use App\Models\contractservice\ContractService;
use App\Models\items\ContractServiceItem;
use App\Models\items\ServiceItem;

/**
 * Class EquipmentRelationship
 */
trait EquipmentRelationship
{   
    public function contract_equipment()
    {
        return $this->hasOne(ContractEquipment::class)->whereNull('schedule_id');
    }

    public function contract_service()
    {
        return $this->hasOneThrough(ContractService::class, ServiceItem::class, 'equipment_id', 'id');
    }

    public function service_item()
    {
        return $this->hasOne(ServiceItem::class);
    }

    public function contract_service_items()
    {
        return $this->hasMany(ContractServiceItem::class);
    }

    public function contract_equipments()
    {
        return $this->hasMany(ContractEquipment::class);
    }

    public function contracts()
    {
        return $this->belongsToMany(Contract::class, 'contract_equipment');
    }

    public function task_schedules()
    {
        return $this->belongsToMany(Contract::class, 'contract_equipment')->whereNotNull('schedule_id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\customer\Customer');
    }

    public function region()
    {
        return $this->hasOne('App\Models\region\Region', 'id', 'region_id')->withoutGlobalScopes();
    }

    public function project_section()
    {
        return $this->hasOne('App\Models\section\Section', 'id', 'section_id')->withoutGlobalScopes();
    }

    public function category()
    {
        return $this->hasOne('App\Models\equipmentcategory\EquipmentCategory', 'id', 'equipment_category_id')->withoutGlobalScopes();
    }

    public function branch()
    {
        return $this->belongsTo('App\Models\branch\Branch');
    }

    public function indoor()
    {
        return $this->hasOne('App\Models\equipment\Equipment', 'id', 'rel_id')->withoutGlobalScopes();
    }

    public function supplier()
    {
        return $this->hasOne('App\Models\supplier\Supplier', 'id', 'customer_id')->withoutGlobalScopes();
    }

    public function products()
    {
        return $this->hasMany('App\Models\items\OrderItem')->withoutGlobalScopes();
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
        return $this->hasMany('App\Models\items\MetaEntry', 'rel_id')->where('rel_type', '=', 5)->withoutGlobalScopes();
    }
}
