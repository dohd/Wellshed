<?php

namespace App\Models\marquee;

use Illuminate\Database\Eloquent\Model;

class OldSuperAdminMarquee extends Model
{

    protected $table = 'old_super_admin_marquees';

    protected $fillable = ['content', 'business', 'start', 'end'];
}
