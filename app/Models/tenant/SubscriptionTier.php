<?php

namespace App\Models\tenant;

use App\Models\Access\Role\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionTier extends Model
{

    use SoftDeletes;

    protected $primaryKey = 'st_number';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [];


    public function related_role(): BelongsTo {

        return $this->belongsTo(Role::class, 'role', 'id');
    }

}
