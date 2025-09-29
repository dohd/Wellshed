<?php

namespace App\Http\Controllers\Focus\package;

use App\Http\Controllers\Controller;
use App\Models\Access\Permission\Permission;
use App\Models\package\Package;
use App\Models\package\PackagePermission;
use App\Repositories\Focus\role\PermissionRepository;
use App\Repositories\Focus\role\RoleRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PackageController extends Controller
{

    /**
     * @var \App\Repositories\Backend\Access\Role\RoleRepository
     */
    protected $roles;

    /**
     * @var \App\Repositories\Backend\Access\Permission\PermissionRepository
     */
    protected $permissions;

    protected $mustHaveClientPermissionNames;

    /**
     * @param \App\Repositories\Backend\Access\Role\RoleRepository $roles
     * @param \App\Repositories\Backend\Access\Permission\PermissionRepository $permissions
     */
    public function __construct(RoleRepository $roles, PermissionRepository $permissions)
    {
        $this->roles = $roles;
        $this->permissions = $permissions;//->orderBy('display_name')->get();
        $this->mustHaveClientPermissionNames = [
            'hrm',
            'manage-department',
            'create-department',
            'edit-department',
            'delete-department',
            'client-area',
            'manage-client-area-ticket',
            'create-client-area-ticket',
            'edit-client-area-ticket',
            'delete-client-area-ticket',
            'business_settings'
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (!access()->allow('manage-subscription-package') && Auth::user()->business->is_tenant) return response("", 403);

        $packages = Package::all();

        if ($request->ajax()) {


            return Datatables::of($packages)

            ->addColumn('modules', function($package){

                $permissionClassNames = $package->package_modules;

                $permHtml = '';
                foreach($permissionClassNames as $p) $permHtml .= '<div class="col-12 col-lg-4">' . $p . ' Module </div>';

                return '<div class="row">'. $permHtml . '</div>';
            })

            ->editColumn('price', function ($package) {

                return numberFormat($package->price);
            })

            ->editColumn('active', function ($package) {

                if($package->active) return '<span class="badge" style="background-color:#12C538">Active</span>';
                else return '<span class="badge" style="background-color:#ff0000">InActive</span>';
            })

            ->addColumn('action', function ($model) {

                $route = route('biller.subscription-packages.edit', $model->package_number);
//                $routeShow = route('biller.subscription-packages.show', $model->id);
//                $routeDelete = route('biller.subscription-packages.destroy', $model->id);

                return '<a href="'.$route.'" class="btn btn-secondary round mr-1">Edit</a>'
//                    . '<a href="' .$routeDelete . '"
//                            class="btn btn-danger round" data-method="delete"
//                            data-trans-button-cancel="' . trans('buttons.general.cancel') . '"
//                            data-trans-button-confirm="' . trans('buttons.general.crud.delete') . '"
//                            data-trans-title="' . trans('strings.backend.general.are_you_sure') . '"
//                            data-toggle="tooltip"
//                            data-placement="top"
//                            title="Delete"
//                            >
//                                <i  class="fa fa-trash"></i>
//                            </a>'
                    ;

            })
            ->rawColumns(['active','action', 'modules'])
            ->make(true);
        }


        return view('focus.package.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        if (!access()->allow('create-subscription-package') && Auth::user()->business->is_tenant) return response("", 403);

        $permissionClassNames = $this->getPermissions()['permissionClassNames'];

        return view('focus.package.create')
            ->with(compact('permissionClassNames'))
            ->withPermissions($this->getPermissions()['perms'])
            ->withRoleCount($this->roles->getCount());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (!access()->allow('create-subscription-package') && Auth::user()->business->is_tenant) return response("", 403);


        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:packages,name'],
            'price' => ['required', 'numeric', 'min:0'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['integer', 'distinct']
        ]);

        //Adding HRM & Department Permissions
        $permissionIds = Permission::whereIn('name', $this->mustHaveClientPermissionNames)->pluck('id')->toArray();
        foreach ($permissionIds as $perm) {
            if (!in_array($perm, $validated['permissions'])) array_push($validated['permissions'], $perm);
        }

        try{

            $package = new Package();
            $package->package_number = uniqid('PKG-');
            $package->fill($validated);

            $package->save();

            foreach ($validated['permissions'] as $permissionId){

                $packagePermission = new PackagePermission();
                $packagePermission->pp_number = uniqid('PP-');
                $packagePermission->fill([
                    'package_number' => $package->package_number,
                    'permission_id' => $permissionId,
                ]);

                $packagePermission->save();
            }

        }
        catch(Exception $exception){

            return [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];

            return errorHandler('Error Creating Subscription Package', $exception);
        }

        return redirect()->route('biller.subscription-packages.index')
            ->with('success', 'Subscription Package ' . $package->name . ' Saved Successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($packageNumber)
    {

        if (!access()->allow('edir-subscription-package') && Auth::user()->business->is_tenant) return response("", 403);

        $package = Package::find($packageNumber);
        $packagePermissions = $package->permissions->pluck('id')->toArray();
        $permissionClassNames = $this->getPermissions()['permissionClassNames'];

        return view('focus.package.edit')
            ->with(compact('package', 'permissionClassNames', 'packagePermissions'))
            ->withPermissions($this->getPermissions()['perms'])
            ->withRoleCount($this->roles->getCount());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $packageNumber)
    {

        if (!access()->allow('edit-subscription-package') && Auth::user()->business->is_tenant) return response("", 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('packages', 'package_number')->ignore($packageNumber, 'package_number')],
            'price' => ['required', 'numeric', 'min:0'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['integer', 'distinct']
        ]);

        //Adding HRM & Department Permissions
        $permissionIds = Permission::whereIn('name', $this->mustHaveClientPermissionNames)->pluck('id')->toArray();
        foreach ($permissionIds as $perm) {
            if (!in_array($perm, $validated['permissions'])) array_push($validated['permissions'], $perm);
        }


        try{

            $package = Package::find($packageNumber);
            $package->fill($validated);

            $package->save();

            $package->packagePermissions()->each(function ($permission) {
                $permission->delete();
            });

            foreach ($validated['permissions'] as $permissionId){

                $packagePermission = new PackagePermission();
                $packagePermission->pp_number = uniqid('PP-');
                $packagePermission->fill([
                    'package_number' => $package->package_number,
                    'permission_id' => $permissionId,
                ]);

                $packagePermission->save();
            }

        }
        catch(Exception $exception){

            return [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];

            return errorHandler('Error Creating Subscription Package', $exception);
        }

        return redirect()->route('biller.subscription-packages.index')
            ->with('success', 'Subscription Package ' . $package->name . ' Saved Successfully.');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        if (!access()->allow('delete-subscription-package') && Auth::user()->business->is_tenant) return response("", 403);


    }


    public function getPermissions()
    {
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

        sort($permissionClassNames);


        $perms = $this->permissions->getAll()->toArray();

        usort($perms, function($a, $b) {
            return strcmp($a['display_name'], $b['display_name']);
        });

        return compact('permissionClassNames', 'perms');
    }

    public function getPackageModules($packageNumber)
    {

        $package = Package::find($packageNumber);

        if (!$package) return [];

        $perms = $package->permissions->pluck('display_name');

        $permissionClassNames = [];
        foreach ($perms as $name){
            array_push($permissionClassNames, strtolower(explode(' ', $name)[0]));
        }

        $permissionClassNames = array_values(array_unique($permissionClassNames));

        sort($permissionClassNames);

        return array_map('ucfirst', $permissionClassNames);
    }
}
