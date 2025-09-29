<?php

namespace App\Models\financialYear;

use App\Models\PurchaseClassBudgets\PurchaseClassBudget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialYear extends Model
{

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'ins'
    ];

    public function purchaseClassBudgets() : HasMany {

        return $this->hasMany(PurchaseClassBudget::class, 'financial_year_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('financial_years.ins', '=', auth()->user()->ins);
        });
    }


}
