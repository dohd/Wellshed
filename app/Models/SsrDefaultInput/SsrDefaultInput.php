<?php

namespace App\Models\SsrDefaultInput;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsrDefaultInput extends Model
{


    use SoftDeletes;

    protected $table = 'ssr_default_inputs';

    protected $fillable = [
        'client',
        'findings',
        'action',
        'recommendations',
    ];


}
