<?php

namespace App\Models\package;

use Illuminate\Database\Eloquent\Model;

class PackagePermission extends Model
{

    protected $table = 'package_permissions';

    protected $primaryKey = 'pp_number';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'package_number',
        'permission_id'
    ];
}
