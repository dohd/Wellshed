<?php

namespace App\Models\purchaseClass;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{

    protected $table = 'expense_categories';

    protected $fillable = [
        'name',
        'budget',
        'description',
        'start_date',
        'end_date'
    ];

    public function purchaseClass(): HasMany {

        return $this->hasMany(PurchaseClass::class, 'expense_category', 'id');
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('expense_categories.ins', '=', auth()->user()->ins);
        });
    }


}
