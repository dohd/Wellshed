<?php

namespace App\Models\hrm\Traits;


use App\Models\Access\Permission\Permission;
use App\Models\Access\Permission\PermissionRole;
use App\Models\Access\Permission\PermissionUser;
use App\Models\Access\Role\Role;
use App\Models\Access\User\UserProfile;
use App\Models\Company\Company;
use App\Models\employee\RoleUser;
use App\Models\employeesalary\EmployeeSalary;
use App\Models\hrm\HrmMeta;
use App\Models\loan\Loan;
use App\Models\loan\LoanItem;
use App\Models\quote\Quote;
use App\Models\salary\Salary;
use App\Models\stakeholder\Stakeholder;
use App\Models\transaction\Transaction;

/**
 * Class HrmRelationship
 */
trait HrmRelationship
{

    public function roleUser()
    {
        return $this->hasOne(RoleUser::class, 'user_id');
    }

    public function role()
    {
        return $this->hasOneThrough(Role::class, RoleUser::class, 'user_id', 'id', 'id', 'role_id')
            ->withoutGlobalScopes();
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\invoice\Invoice', 'user_id');
    }

        public function quotes()
    {
        return $this->hasMany(Quote::class, 'user_id');
    }

            public function projects()
    {
      //  return $this->hasMany(Project::class, 'user_id');
    }


    public function amount()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

        public function payroll()
    {
        return $this->hasMany(Transaction::class, 'payer_id','id')->where('relation_id','=',3);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function meta()
    {
        return $this->hasOne(HrmMeta::class, 'user_id');
    }

    public function user_associated_permission()
    {
        //user current permission
        //  return $this->hasManyThrough(Permission::class, PermissionUser::class, 'permission_id','id', 'id','user_id')->withoutGlobalScopes();

        return $this->belongsToMany(Permission::class, PermissionUser::class, 'user_id', 'permission_id', 'id', 'id')->withoutGlobalScopes();
    }

    public function role_associated_permission()
    {
        //user role id available permission
        //  return $this->hasManyThrough(Permission::class, PermissionUser::class, 'permission_id','id', 'id','user_id')->withoutGlobalScopes();

        return $this->belongsToMany(PermissionRole::class, RoleUser::class, 'user_id', 'role_id', 'id', 'role_id')->withoutGlobalScopes();
    }

    public function created_by_user()
    {
        return $this->hasOne(Self::class, 'created_by');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, PermissionUser::class, 'user_id', 'permission_id', 'id', 'id')->withoutGlobalScopes();
    }
    public function monthlysalary()
    {
        return $this->belongsTo(EmployeeSalary::class, 'id','user_id')->where('status','Active');
    }
    
    public function employees_salary() {
     
        return $this->hasOne(Salary::class, 'employee_id')->withoutGlobalScopes();
    }


    public function loan()
    {
        return $this->hasOne(Loan::class, 'employee_id');
    }

    public function loan_items()
    {
        return $this->hasManyThrough(LoanItem::class, Loan::class, 'employee_id', 'loan_id', 'id', 'id')->withoutGlobalScopes();
    }

    public function business()
    {
        return $this->hasOne(Company::class, 'id', 'ins');
    }

    public function stakeholders()
    {

        return $this->belongsTo(Stakeholder::class, 'sh_authorizer_id', 'id');
    }
}
