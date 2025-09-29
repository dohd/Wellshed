<?php

namespace App\Http\Controllers\Focus\casual;

use Illuminate\Database\Eloquent\Model;

class CasualLabourerAllocation extends Model
{
    protected $table = 'casual_labourers_allocations';

    protected $primaryKey = 'lacl_number';

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
    protected $guarded = [];
}
