<?php

namespace App\Models\Access\Role;

use App\Models\Access\Role\Traits\Attribute\RoleAttribute;
use App\Models\Access\Role\Traits\Relationship\RoleRelationship;
use App\Models\Access\Role\Traits\RoleAccess;
use App\Models\Access\Role\Traits\Scope\RoleScope;
use App\Models\BaseModel;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Role.
 */
class Role extends BaseModel
{
    use RoleScope,
        ModelTrait,
        RoleAccess,
        RoleAttribute,
        RoleRelationship,
        SoftDeletes {
            RoleAttribute::getEditButtonAttribute insteadof ModelTrait;
            RoleAttribute::getDeleteButtonAttribute insteadof ModelTrait;
        }

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'all', 'sort', 'ins', 'created_by', 'updated_by', 'deleted_at'];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('access.roles_table');
    }

    /**
     * model life cycle event listeners
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->created_by = $instance->created_by ?: auth()->user()->id;
            $instance->updated_by = $instance->updated_by ?: auth()->user()->id;
            $instance->ins = $instance->ins ?: auth()->user()->ins;
            return $instance;
        });

        static::updating(function ($instance) {
            $instance->updated_by = $instance->updated_by ?: auth()->user()->id;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('roles.ins', auth()->user()->ins);
        });
    }    
}
