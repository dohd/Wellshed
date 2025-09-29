<?php

namespace App\Models\employeeDailyLog;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EdlSubcategoryAllocation extends Model
{

    use SoftDeletes;

    protected $table = 'edl_subcategory_allocations';

    protected $primaryKey = 'employee';

    public $incrementing = false;

    protected $fillable = [
        'employee',
        'allocations',
        'customer_id','branch_id',
        'ins'
    ];


    //relation ships
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });


        static::addGlobalScope('ins', function ($builder) {
            $builder->where('edl_subcategory_allocations.ins', '=', auth()->user()->ins);
        });
    }


}
