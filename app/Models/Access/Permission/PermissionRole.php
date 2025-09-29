<?php

namespace App\Models\Access\Permission;

use App\Models\Access\Permission\Traits\Relationship\PermissionRoleRelationship;
use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
    use PermissionRoleRelationship;
    
    protected $table = 'permission_role';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['role_id', 'permission_id'];

    protected $attributes = [];

    public $timestamps = false;
}
