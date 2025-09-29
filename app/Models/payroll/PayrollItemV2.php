<?php

namespace App\Models\payroll;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\payroll\Traits\PayrollItemRelationship;

class PayrollItemV2 extends Model
{

    use SoftDeletes,PayrollItemRelationship;

    protected $table = 'payroll_items';

    protected $primaryKey = 'id';

    protected $fillable = [
        "absent_daily_deduction",
        "absent_days",
        "absent_total_deduction",
        "advance",
        "basic_hourly_salary",
        "basic_plus_allowance",
        "basic_salary",
        "benefits",
        "deduction_narration",
        "employee_id",
        "fixed_salary",
        "house_allowance",
        "housing_levy",
        "income_tax",
        "loan",
        "man_hours",
        "max_hourly_salary",
        "nhif",
        "deduction_exempt",
        "nssf",
        "other_allowance",
        "other_allowances",
        "additional_taxable_deductions",
        "pay_per_hr",
        "paye",
        "personal_relief",
        "rate_per_month",
        "taxable_pay",
        "total_allowance",
        "transport_allowance",
        "additional_hours",
        "additional_hourly_salary",
        "ins",
        "user_id",
        "erp_sales_commission",
        "erp_sales_count",
        "erp_sales_rate",
        "erp_sales_value",
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    protected static function boot()
    {
            parent::boot();
            static::creating(function ($instance) {
                $instance->user_id = auth()->user()->id;
                $instance->ins = auth()->user()->ins;
               return $instance;
           });
    }

}
