<?php

namespace App\Http\Controllers;

use App\Models\Access\Permission\Permission;
use App\Models\Access\Permission\PermissionRole;
use App\Models\Access\Permission\PermissionUser;
use App\Models\Access\Role\Role;
use App\Models\Access\User\User;
use App\Models\employee\RoleUser;
use App\Models\hrm\Hrm;

trait ClientSupplierAuth
{
    public function createAuth($entity, $input, $user_type)
    {
        if (!isset($input['first_name'], $input['last_name'], $input['email'], $input['password']))
            return false;
        if (isset($input['picture'])) {
            $input['picture'] = $this->uploadAuthImage($input['picture']);
        }
        
        $user = Hrm::create([
            'username' => random_username(),
            'tenant_customer_id' => $user_type == 'client'? $entity->id : null,
            'customer_id' => $user_type == 'client'? $entity->id : null,
            'supplier_id' => $user_type == 'supplier'? $entity->id : null,
            'client_vendor_id' => $user_type == 'client_vendor'? $entity->id : null,
            'client_user_id' => $user_type == 'client_user'? $entity->id : null,
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'confirmed' => 1,
            'ins' => auth()->user()->ins,
            'created_by' => auth()->user()->id,
        ]);
        
        // assign permissions
        $perm_ids = [];
        if (auth()->user()->business->is_main && $user->tenant_customer_id) {
            // tenant initial user role and permissions (tenant setup)
            $user->update(['customer_id' => null]); // updated to tenant_customer_id
            $auth_role = auth()->user()->roles()->first();
            $user_role = $auth_role->replicate();
            $user_role->fill(['created_by' => $user->id, 'updated_by' => $user->id]);
            unset($user_role->pivot, $user_role->created_at, $user_role->updated_at);
            $user_role->save();
            RoleUser::create(['role_id' => $user_role->id, 'user_id' => $user->id]);

            $perm_ids = PermissionRole::select('permission_id')->distinct()->where('role_id', $auth_role->id)
            ->pluck('permission_id')->toArray();
        } elseif ($user->customer_id || $user->client_user_id) {
            // customer user permissions (external login - active account)
            $perms = [
                'sale','manage-quote', 'manage-pi', 'crm', 'manage-client', 'manage-pricelist',
                'manage-crm-user', 'create-crm-user', 'edit-crm-user', 'delete-crm-user',
                'manage-crm-vendor', 'create-crm-vendor', 'edit-crm-vendor', 'delete-crm-vendor',
                'manage-crm-ticket', 'create-crm-ticket', 'edit-crm-ticket', 'delete-crm-ticket',
                'maintenance-project', 'manage-project', 'manage-equipment', 'manage-pm-contract','manage-schedule',
                'manage-daily-logs', 'review-daily-logs',
            ];
            $perm_ids = Permission::whereIn('name', $perms)->pluck('id')->toArray();
        } elseif ($user->supplier_id) {
            // supplier user permissions (external login - active account)
            $perms = ['procurement-management', 'finance', 'manage-supplier', 'manage-pricelist', 'stock', 'manage-grn'];
            $perm_ids = Permission::whereIn('name', $perms)->pluck('id')->toArray();
        } elseif ($user->client_vendor_id) {
            // customer-vendor permssions (external login - active account)
            $perms = ['crm','manage-client','manage-crm-ticket', 'create-crm-ticket', 'edit-crm-ticket', 'delete-crm-ticket',];
            $perm_ids = Permission::whereIn('name', $perms)->pluck('id')->toArray(); 
        } 
        
        foreach ($perm_ids as $key => $value) {
            $perm_ids[$key] = ['permission_id' => $value, 'user_id' => $user->id];
        }
        PermissionUser::insert($perm_ids);

        return $user;
    }

    public function updateAuth($entity, $input, $user_type)
    {
        $user = null;
        if ($user_type == 'client_vendor') $user = User::where('client_vendor_id', $entity->id)->first();
        if ($user_type == 'client') $user = User::where('customer_id', $entity->id)->orWhere('tenant_customer_id', $entity->id)->first();
        if ($user_type == 'supplier') $user = User::where('supplier_id', $entity->id)->first(); 
        if (!$user) return $this->createAuth($entity, $input, $user_type);
        
        if (!isset($input['first_name'], $input['last_name'], $input['email'])) {
            return false;
        }
        if (isset($input['picture'])) {
            $this->removeAuthImage($user);
            $input['picture'] = $this->uploadAuthImage($input['picture']);
        }
        
        $data = [
            'tenant_customer_id' => $user_type == 'client'? $entity->id : null,
            'customer_id' => $user_type == 'client'? $entity->id : null,
            'supplier_id' => $user_type == 'supplier'? $entity->id : null,
            'client_vendor_id' => $user_type == 'client_vendor'? $entity->id : null,
            'client_user_id' => $user_type == 'client_user'? $entity->id : null,
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'confirmed' => 1,
            'ins' => auth()->user()->ins,
            'updated_by' => auth()->user()->id,
        ];
        if (isset($input['password'])) $data['password'] = $input['password'];
        $user->update($data);
        
        // assign permissions
        $perm_ids = [];
        if (auth()->user()->business->is_main && $user->tenant_customer_id) {
            // tenant initial user role and permissions (tenant setup)
            $user->update(['customer_id' => null]);
            $auth_role = auth()->user()->roles()->first();
            $user_role = Role::where('created_by', $user->id)->first();
            if (!$user_role) {
                $user_role = $auth_role->replicate();
                $user_role->fill(['created_by' => $user->id, 'updated_by' => $user->id]);
                unset($user_role->pivot, $user_role->created_at, $user_role->updated_at);
                $user_role->save();
                RoleUser::create(['role_id' => $user_role->id, 'user_id' => $user->id]);
            }

            $perm_ids = PermissionRole::select('permission_id')->distinct()->where('role_id', $auth_role->id)
            ->pluck('permission_id')->toArray();
        } elseif ($user->customer_id || $user->client_user_id) {
            // customer user permissions (external login - active account)
            $perms = [
                'sale','manage-quote', 'manage-pi', 'crm', 'manage-client', 'manage-pricelist',
                'manage-crm-user', 'create-crm-user', 'edit-crm-user', 'delete-crm-user',
                'manage-crm-vendor', 'create-crm-vendor', 'edit-crm-vendor', 'delete-crm-vendor',
                'manage-crm-ticket', 'create-crm-ticket', 'edit-crm-ticket', 'delete-crm-ticket',
                'maintenance-project', 'manage-project', 'manage-equipment', 'manage-pm-contract','manage-schedule',
                'manage-daily-logs', 'review-daily-logs',
            ];
            $perm_ids = Permission::whereIn('name', $perms)->pluck('id')->toArray();
        } elseif ($user->supplier_id) {
            // supplier user permissions (external login - active account)
            $perms = ['procurement-management', 'finance', 'manage-supplier', 'manage-pricelist', 'stock', 'manage-grn'];
            $perm_ids = Permission::whereIn('name', $perms)->pluck('id')->toArray();
        } elseif ($user->client_vendor_id) {
            // customer-vendor permssions (external login - active account)
            $perms = ['crm','manage-client','manage-crm-ticket', 'create-crm-ticket', 'edit-crm-ticket', 'delete-crm-ticket',];
            $perm_ids = Permission::whereIn('name', $perms)->pluck('id')->toArray();
        }

        PermissionUser::where('user_id', $user->id)->delete();
        foreach ($perm_ids as $key => $value) {
            $perm_ids[$key] = ['permission_id' => $value, 'user_id' => $user->id];
        }
        PermissionUser::insert($perm_ids);
        
        return true;
    }

    public function deleteAuth($entity, $user_type)
    { 
        $user = null;
        if ($user_type == 'client_user') $user = User::where('client_user_id', $entity->id)->first();
        if ($user_type == 'client_vendor') $user = User::where('client_vendor_id', $entity->id)->first();
        if ($user_type == 'client') $user = User::where('customer_id', $entity->id)->first();
        if ($user_type == 'supplier') $user = User::where('supplier_id', $entity->id)->first();

        if ($user) {
            $this->removeAuthImage($user);
            if($user->role){
                $user->role()->first()->delete();
                RoleUser::where(['user_id' => $user->id])->delete();
                PermissionUser::where('user_id', $user->id)->delete();
            }
            $user->delete(); 
            return true;
        }
    }

    public function uploadAuthImage($image)
    {
        $path = 'img' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR;
        $image_name = time() . $image->getClientOriginalName();
        $this->storage->put($path . $image_name, file_get_contents($image->getRealPath()));
        return $image_name;
    } 
    public function removeAuthImage($entity)
    {
        $path = 'img' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR;
        $storage_exists = $this->storage->exists($path . $entity->picture);
        if ($entity->picture && $storage_exists) {
            $this->storage->delete($path . $entity->picture);
        }
        return true;
    }
}