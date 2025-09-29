<?php

namespace App\Models\purchaseClass;

use App\Models\PurchaseClassBudgets\PurchaseClassBudget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseClass extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'name',
        'expense_category',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('purchase_classes.ins', '=', auth()->user()->ins);
        });
    }

    public function budgets(): HasMany {

        return $this->hasMany(PurchaseClassBudget::class, 'purchase_class_id', 'id');
    }

    public function expenseCategory(): BelongsTo {

        return $this->belongsTo(ExpenseCategory::class, 'expense_category', 'id');
    }

}
