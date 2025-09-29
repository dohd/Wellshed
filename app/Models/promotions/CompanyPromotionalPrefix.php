<?php

namespace App\Models\promotions;

use Illuminate\Database\Eloquent\Model;

class CompanyPromotionalPrefix extends Model
{

    protected $table = 'company_promotional_prefixes';

    protected $fillable = [
        'company_id',
        'prefix',
    ];
}
