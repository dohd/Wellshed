<?php

namespace App\Models\PurchaseClassBudgets;

use App\Models\classlist\Classlist;
use App\Models\department\Department;
use App\Models\financialYear\FinancialYear;
use App\Models\items\PurchaseItem;
use App\Models\items\PurchaseorderItem;
use App\Models\purchase\Purchase;
use App\Models\purchaseClass\PurchaseClass;
use App\Models\purchaseorder\Purchaseorder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseClassBudget extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'purchase_class_id',
        'budget',
        'description',
        'financial_year_id',
        'department_id',
        'january',
        'february',
        'march',
        'april',
        'may',
        'june',
        'july',
        'august',
        'september',
        'october',
        'november',
        'december',
        'classlist_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->ins = auth()->user()->ins;
            $instance->user_id = auth()->user()->id;
            return $instance;
        });


        static::addGlobalScope('ins', function ($builder) {
            $builder->where('purchase_class_budgets.ins', '=', auth()->user()->ins);
        });
    }

    public function purchaseClass(): BelongsTo {

        return $this->belongsTo(PurchaseClass::class, 'purchase_class_id', 'id');
    }

    public function purchaseItems(): HasMany {

        return $this->hasMany(PurchaseItem::class, 'purchase_class_budget', 'id');
    }

    public function purchaseOrderItems(): HasMany {

        return $this->hasMany(PurchaseorderItem::class, 'purchase_class_budget', 'id');
    }

    public function financialYear(): BelongsTo {

        return $this->belongsTo(FinancialYear::class, 'financial_year_id', 'id');
    }

    public function department(): BelongsTo {

        return $this->belongsTo(Department::class, 'department_id', 'id');
    }


    public function classList(): BelongsTo {

        return $this->belongsTo(Classlist::class, 'classlist_id', 'id');
    }


}
