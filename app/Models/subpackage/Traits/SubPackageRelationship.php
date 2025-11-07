<?php

namespace App\Models\subpackage\Traits;

use App\Models\subscription\Subscription;

trait SubPackageRelationship
{
	public function subscriptions() {
		return $this->hasMany(Subscription::class, 'sub_package_id');
	}
}
