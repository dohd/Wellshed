<?php

namespace App\Models\casual_labourer_remuneration;

use App\Models\labour_allocation\LabourAllocation;
use Illuminate\Database\Eloquent\Model;

class CLRWage extends Model
{
    protected $table = 'clr_wages';

    protected $primaryKey = 'clrcl_number';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [];

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        // 'clr_number'
    ];

    /** Relationship **/
    public function labourAllocation()
    {
        return $this->belongsTo(LabourAllocation::class);
    }
}
