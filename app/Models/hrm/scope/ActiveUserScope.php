<?php

namespace App\Models\hrm\scope;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActiveUserScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Apply the global scope to select only active users (status = 1)
        $builder->where('status', 1);
    }
}
