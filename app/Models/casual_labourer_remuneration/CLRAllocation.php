<?php

namespace App\Models\casual_labourer_remuneration;

use Illuminate\Database\Eloquent\Model;

class CLRAllocation extends Model
{
    protected $table = 'clr_allocations';

    protected $primaryKey = 'clrla_number';

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
}
