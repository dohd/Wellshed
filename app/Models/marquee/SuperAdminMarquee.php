<?php

namespace App\Models\marquee;

use Illuminate\Database\Eloquent\Model;

class SuperAdminMarquee extends Model
{
    protected $table = 'super_admin_marquees';

    protected $fillable = ['content', 'business', 'start', 'end'];



}
