<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\hrm\ManageHrmRequest;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\Permission\Permission;
use App\Models\Access\Role\Role;
use App\Models\package\Package;
use App\Models\tenant\Tenant;
use App\Repositories\Focus\role\PermissionRepository;
use App\Repositories\Focus\role\RoleRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Class RoleController.
 */
class RoleController extends Controller
{
    /**
     * @var \App\Repositories\Backend\Access\Role\RoleRepository
     */
    protected $roles;

    /**
     * @var \App\Repositories\Backend\Access\Permission\PermissionRepository
     */
    protected $permissions;

    /**
     * @param \App\Repositories\Backend\Access\Role\RoleRepository $roles
     * @param \App\Repositories\Backend\Access\Permission\PermissionRepository $permissions
     */
    public function __construct(RoleRepository $roles, PermissionRepository $permissions)
    {
        $this->roles = $roles;
        $this->permissions = $permissions;
    }

    /**
     * @param \App\Http\Requests\Backend\Access\Role\ManageRoleRequest $request
     *
     * @return mixed
     */
    public function index(ManageHrmRequest $request)
    {
        return new ViewResponse('focus.hrms.roles.index');
    }

    /**
     * @param \App\Http\Requests\Backend\Access\Role\CreateRoleRequest $request
     *
     * @return \App\Http\Responses\Backend\Access\Role\CreateResponse
     */
    public function create(ManageHrmRequest $request)
    {

//        return auth()->user()->tenant->package->service;

        $permissionDisplayNames = Permission::all()->pluck('display_name');

        $exclusions = [
            'CRM Ticket Tag',
            'CRM Client Vendor',
            'Client Area',
            'Business Account',
            'Account Service',
        ];

        if (auth()->user()->ins != 2){

            foreach ($exclusions as $exclusion) {
                $permissionDisplayNames = $permissionDisplayNames->reject(function ($displayName) use ($exclusion) {
                    return strpos($displayName, $exclusion) !== false;
                });
            }
        }

        $permissionClassNames = [];
        foreach ($permissionDisplayNames as $name){
            array_push($permissionClassNames, strtolower(explode(' ', $name)[0]));
        }

        $permissionClassNames = array_values(array_unique($permissionClassNames));

        $authPermissions = $this->permissions->getAll();

        $isTenant = Auth::user()->business->is_tenant;

        if ($isTenant){

            $package = optional(optional(optional(Tenant::find(Auth::user()->business->id))->package)->service)->package;

            if (count($package)) $authPermissions = $package->first()->permissions;
        }



        return view('focus.hrms.roles.create')
            ->with(compact('permissionClassNames'))
            ->withPermissions($authPermissions ?? $this->permissions->getAll())
            ->withRoleCount($this->roles->getCount());
    }

    /**
     * @param \App\Http\Requests\Backend\Access\Role\StoreRoleRequest $request
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(ManageHrmRequest $request)
    {

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', 'integer', 'distinct', 'min:1'],
        ]);


        try {
            $this->roles->create($request->except('_token'));
        } catch (\Throwable $th) { 
            return errorHandler('Error Creating Role', $th);
        }

        return new RedirectResponse(route('biller.role.index'), ['flash_success' => trans('alerts.backend.roles.created')]);
    }

    /**
     * @param \App\Models\Access\Role\Role $role
     * @param \App\Http\Requests\Backend\Access\Role\EditRoleRequest $request
     *
     * @return \App\Http\Responses\Backend\Access\Role\EditResponse
     */
    public function edit(Role $role, ManageHrmRequest $request)
    {
        if (auth()->user()->ins == $role->ins) {

            $permissionDisplayNames = Permission::all()->pluck('display_name');

            $exclusions = [
                'CRM Ticket Tag',
                'CRM Client Vendor',
                'Client Area',
                'Business Account',
                'Account Service',
            ];

            if (auth()->user()->ins != 2){

                foreach ($exclusions as $exclusion) {
                    $permissionDisplayNames = $permissionDisplayNames->reject(function ($displayName) use ($exclusion) {
                        return strpos($displayName, $exclusion) !== false;
                    });
                }
            }

            $permissionClassNames = [];
            foreach ($permissionDisplayNames as $name){
                array_push($permissionClassNames, strtolower(explode(' ', $name)[0]));
            }

            $permissionClassNames = array_values(array_unique($permissionClassNames));


            $authPermissions = $this->permissions->getAll();

            $isTenant = Auth::user()->business->is_tenant;

            if ($isTenant){

                $package = optional(optional(optional(Tenant::find(Auth::user()->business->id))->package)->service)->package;

                if (count($package)) $authPermissions = $package->first()->permissions;
            }


            return view('focus.hrms.roles.edit')
                ->with(compact('permissionClassNames'))
                ->withRole($role)
                ->withRolePermissions($role->permissions->pluck('id')->all())
                ->withPermissions($authPermissions ?? $this->permissions->getAll());
        }
    }

    /**
     * @param \App\Models\Access\Role\Role $role
     *
     */
    public function update(Request $request, Role $role)
    {

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', 'integer', 'distinct', 'min:1'],
        ]);


        try {
            $this->roles->update($role, $request->except('_token'));
        } catch (Exception $e) {
            return "Error: '" . $e->getMessage() . " | on File: " . $e->getFile() . "  | & Line: " . $e->getLine();
        }

        return new RedirectResponse(route('biller.role.index'), ['flash_success' => trans('alerts.backend.roles.updated')]);
    }

    /**
     * @param \App\Models\Access\Role\Role $role
     * @param \App\Http\Requests\Backend\Access\Role\DeleteRoleRequest $request
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy($id, ManageHrmRequest $request)
    {
        try {

            $role = Role::find($id);
            $role->delete();

        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Roles', $th);
        }

        return new RedirectResponse(route('biller.role.index'), ['flash_success' => trans('alerts.backend.roles.deleted')]);
    }
}
