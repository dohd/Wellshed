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

namespace App\Http\Controllers\Focus\tenant_service;

use App\Http\Controllers\Focus\package\PackageController;
use App\Models\package\Package;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\tenant_service\PackageExtra;
use App\Models\tenant_service\TenantService;
use App\Repositories\Focus\tenant_service\TenantServiceRepository;

/**
 * ProductcategoriesController
 */
class TenantServicesController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $repository ;
     */
    public function __construct(TenantServiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(Request $request)
    {
        return view('focus.tenant_services.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {
        
        $package_extras = PackageExtra::where('active', 0)->get();

        $packages = Package::where('active', 1)->get()->map(function ($pkg){

            return (Object) [
                'package_number' => $pkg->package_number,
                'name' => $pkg->name,
                'price' => $pkg->price,
                'modules' => $pkg->package_modules
            ];
        });
        
        return view('focus.tenant_services.create', compact( 'packages','package_extras'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    { 
        $request->validate([
            'package_number' => ['required', 'string', 'exists:packages,package_number'],
            'cost' => ['required', 'numeric'],
            'maintenance_cost' => ['required', 'numeric'],
        ]);

        try {
            $this->repository->create($request->except(['_token']));
        } catch (Exception $exception) {

            return [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];

            return errorHandler('Error Creating Tenant Service!', $exception);
        }
        
        return new RedirectResponse(route('biller.tenant_services.index'), ['flash_success' => 'Tenant Service  Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(TenantService $tenant_service, Request $request)
    {
        $package_extras = PackageExtra::get();
        foreach ($package_extras as $key => $package) {
            foreach ($tenant_service->items as $key => $item) {
                if ($item->package_id == $package->id) {
                    $package_extras[$key]['extra_cost'] = $item->extra_cost;
                    $package_extras[$key]['maint_cost'] = $item->maint_cost;
                    $package_extras[$key]['checked'] = 'checked';
                }
            }
        }

        $packages = Package::where('active', 1)->get()->map(function ($pkg){

            return (Object) [
                'package_number' => $pkg->package_number,
                'name' => $pkg->name,
                'price' => $pkg->price,
                'modules' => $pkg->package_modules
            ];
        });


        return view('focus.tenant_services.edit', compact('tenant_service', 'package_extras', 'packages'));
    }

    /**
     * Update Resource in Storage
     * 
     */
    public function update(Request $request, TenantService $tenant_service)
    {
        $request->validate([
            'package_number' => ['required', 'string', 'exists:packages,package_number'],
            'cost' => ['required', 'numeric'],
            'maintenance_cost' => ['required', 'numeric'],
        ]);


        try {
            $this->repository->update($tenant_service, $request->except(['_token']));
        } catch (Exception $exception) {

            return [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];

            return errorHandler('Error Updating Tenant Service!', $exception);
        }

        return new RedirectResponse(route('biller.tenant_services.index'), ['flash_success' => 'Tenant Service  Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(TenantService $tenant_service)
    {
        try {
            $this->repository->delete($tenant_service);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Tenant Service!', $th);
        }

        return new RedirectResponse(route('biller.tenant_services.index'), ['flash_success' => 'Tenant Service successfully deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(TenantService $tenant_service, Request $request)
    {
        return view('focus.tenant_services.view', compact('tenant_service'));
    }
}
