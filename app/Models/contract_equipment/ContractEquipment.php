<?php

namespace App\Models\contract_equipment;

use App\Models\equipment\Equipment;
use Illuminate\Database\Eloquent\Model;

class ContractEquipment extends Model
{
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'contract_equipment';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [];

    /**
     * Default values for model fields
     * @var array
     */
    protected $attributes = [];

    /**
     * Dates
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = []; 

    // relations
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }        
}