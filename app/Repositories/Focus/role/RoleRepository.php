<?php

namespace App\Repositories\Focus\role;

use App\Exceptions\GeneralException;
use App\Models\Access\Permission\PermissionRole;
use App\Models\Access\Permission\PermissionUser;
use App\Models\Access\Role\Role;
use App\Models\employee\RoleUser;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Class RoleRepository.
 */
class RoleRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Role::class;

    /**
     * @param string $order_by
     * @param string $sort
     *
     * @return mixed
     */
    public function getAll($order_by = 'sort', $sort = 'asc')
    {
        return $this->query()
            ->with('users', 'permissions')
            ->orderBy($order_by, $sort)
            ->get();
    }

    /**
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        // $q->where('roles.status', 0);


        $q->leftjoin('role_user', 'role_user.role_id', '=', 'roles.id')
        ->leftjoin('users', 'role_user.user_id', '=', 'users.id')
        ->leftjoin('permission_role', 'permission_role.role_id', '=', 'roles.id')
        ->leftjoin('permissions', 'permission_role.permission_id', '=', 'permissions.id');

        $q->select([
            'roles.id',  'roles.name', 'all',   'roles.sort', 'roles.status',  'roles.created_at', 'roles.updated_at',  'roles.ins',
            DB::raw("GROUP_CONCAT( DISTINCT rose_permissions.display_name SEPARATOR '<br/>') as permission_name"),
            DB::raw('(SELECT COUNT(rose_role_user.id) FROM rose_role_user LEFT JOIN rose_users ON rose_role_user.user_id = rose_users.id WHERE rose_role_user.role_id = rose_roles.id AND rose_users.deleted_at IS NULL) AS userCount'),
        ])
        ->groupBy(config('access.roles_table') . '.id', config('access.roles_table') . '.name', config('access.roles_table') . '.all', config('access.roles_table') . '.sort');

        return $q;
    }

    /**
     * @param array $input
     *
     * @throws GeneralException
     *
     * @return bool
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $role_exists = $this->query()->where('name', $input['name'])->first();
        if ($role_exists) throw ValidationException::withMessages([trans('exceptions.backend.access.roles.already_exists')]);

        $input['permissions'] = @$input['permissions'] ?: [];
        // check if the role must contain a permission as per config
        if (config('access.roles.role_must_contain_permission') && !$input['permissions'])
            throw ValidationException::withMessages([trans('exceptions.backend.access.roles.needs_permission')]);

        $role = (new Role())->fill([
            'name' => $input['name'],
            'sort' => 0,
            'all' => 0,
            'status' => @$input['status'] ?: 0,
        ]);
        $role->save();

        // if($input['subscription_tier']){

        //     $subscriptionTier = new SubscriptionTier();
        //     $subscriptionTier->st_number = uniqid('ST-');
        //     $subscriptionTier->role = $role->id;
        //     $subscriptionTier->save();
        // }

        if ($role) {
            $role->attachPermissions($input['permissions']);
            DB::commit();
            return $role;
        }
    }

    /**
     * @param App\Models\Access\Role\Role $role
     * @param array $input
     *
     * @return bool
     */
    public function update($role, array $input)
    {
        // dd($input);
        DB::beginTransaction();

//        $role_exists = $this->query()->where('id', '!=', $role->id)->where('name', $input['name'])->first();
//        if ($role_exists) throw ValidationException::withMessages([trans('exceptions.backend.access.roles.already_exists')]);

        // check if the role must contain a permission as per config
        $input['permissions'] = @$input['permissions'] ?: [];
        if (config('access.roles.role_must_contain_permission') && !$input['permissions'])
            throw ValidationException::withMessages([trans('exceptions.backend.access.roles.needs_permission')]);

        $role_data = [
            'name' => $input['name'],
            'sort' => 0,
            'all' => 0,
            'status' => @$input['status'] ?: 0,
        ];

        if ($role->update($role_data)) {
            // delete unchecked permission from users with this role
            $unchecked_role_permissions = PermissionRole::where('role_id', $role->id)
                ->whereNotIn('permission_id', $input['permissions'])
                ->pluck('permission_id')->toArray();
            $user_ids = RoleUser::where('role_id', $role->id)->pluck('user_id')->toArray();
            PermissionUser::whereIn('user_id', $user_ids)
                ->whereIn('permission_id', $unchecked_role_permissions)
                ->delete();

            // create or update role permissions
            PermissionRole::where('role_id', $role->id)
                ->whereNotIn('permission_id', $input['permissions'])->delete();
            foreach ($input['permissions'] as $value) {
                PermissionRole::firstOrCreate(
                    ['role_id' => $role->id, 'permission_id' => $value],
                    ['role_id' => $role->id, 'permission_id' => $value],
                );
            }

            DB::commit();
            return $role;
        }
    }

    /**
     * @param Role $role
     *
     * @throws GeneralException
     *
     * @return bool
     */
    public function delete(Role $role)
    {
        DB::beginTransaction();

        // user attached role
        if ($role->users()->count())
            throw ValidationException::withMessages([trans('exceptions.backend.access.roles.has_users')]);

        // delete permissions from users with this role
        $role_permissions = $role->permissions->pluck('id')->toArray();
        $user_ids = RoleUser::where('role_id', $role->id)->pluck('user_id')->toArray();
        PermissionUser::whereIn('user_id', $user_ids)
            ->whereIn('permission_id', $role_permissions)
            ->delete();

        $role->permissions()->detach();
        if ($role->delete()) {
            DB::commit();
            return true;
        }
    }

    /**
     * @return mixed
     */
    public function getDefaultUserRole()
    {
        $q = $this->query();
        if (is_numeric(config('access.users.default_role')))
            return $q->where('id', (int) config('access.users.default_role'))->first();

        return $q->where('name', config('access.users.default_role'))->first();
    }
}
