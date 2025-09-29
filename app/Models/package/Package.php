<?php

namespace App\Models\package;

use App\Models\Access\Permission\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{

    protected $table = 'packages';

    protected $primaryKey = 'package_number';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'price',
    ];

//    protected $appends = ['package_modules'];

    public function packagePermissions() :hasMany {

        return $this->hasMany(PackagePermission::class, 'package_number', 'package_number');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'package_permissions', 'package_number', 'permission_id');
    }

    public function getPackageModulesAttribute(){

        $perms = $this->permissions->pluck('display_name');

        $permissionClassNames = [];
        foreach ($perms as $name){
            array_push($permissionClassNames, strtolower(explode(' ', $name)[0]));
        }

        $permissionClassNames = array_values(array_unique($permissionClassNames));

        sort($permissionClassNames);

        return array_map('ucfirst', $permissionClassNames);
    }

}
