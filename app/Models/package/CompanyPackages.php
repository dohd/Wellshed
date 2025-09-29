<?php

namespace App\Models\package;

use Illuminate\Database\Eloquent\Model;

class CompanyPackages extends Model
{

    protected $table = 'company_packages';

    protected $primaryKey = 'cp_number';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'package_number',
        'company_id'
    ];
}
