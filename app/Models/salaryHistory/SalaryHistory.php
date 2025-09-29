<?php

namespace App\Models\salaryHistory;

use Illuminate\Database\Eloquent\Model;

class SalaryHistory extends Model
{

    protected $table = 'salary_histories';

    protected $fillable = [
        'salary_id',
        'date',
        'commencement_date',
        'basic_salary',
        'job_grade',
        'hourly_salary',
        'nhif',
        'deduction_exempt'
    ];
}
