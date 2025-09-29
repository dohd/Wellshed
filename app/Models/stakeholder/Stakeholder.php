<?php

namespace App\Models\stakeholder;

use App\Models\Access\Role\Role;
use App\Models\employee\RoleUser;
use App\Models\hrm\Hrm;
use Illuminate\Database\Eloquent\Model;

class Stakeholder extends Model
{

    protected $table = 'users';

    protected $fillable = [
        "is_stakeholder",
        "status",
        "first_name",
        "last_name",
        "sh_id_number",
        "sh_gender",
        "sh_primary_contact",
        "sh_secondary_contact",
        "email",
        "sh_company",
        "sh_designation",
        'sh_authorizer_id',
        "sh_access_reason",
        "sh_access_start",
        "sh_access_end",
        "login_access",
        'role',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('users.ins', auth()->user()->ins);
        });
        static::addGlobalScope('stakeholder', function ($builder) {
            $builder->where('users.is_stakeholder', 1);
        });
    }


    public function role()
    {
        return $this->hasOneThrough(Role::class, RoleUser::class, 'user_id', 'id', 'id', 'role_id')->withoutGlobalScopes();
    }


    public function permissions()
    {
        return $this->belongsToMany(config('access.permission'), config('access.permission_user_table'), 'user_id', 'permission_id');
    }

    public function authorizer()
    {

        return $this->belongsTo(Hrm::class, 'sh_authorizer_id', 'id');
    }


}
