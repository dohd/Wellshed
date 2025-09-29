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

use App\Http\Controllers\Controller;
use App\Repositories\Focus\tenant_service\TenantServiceRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TenantServicesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(TenantServiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('package', function ($tenant_service) {

                return optional($tenant_service->package->first())->name ?? '<span style="color: #F9D448"><i> <b>Package Not Selected</b> </i></span>';
            })
            ->editColumn('cost', function ($tenant_service) {
                return numberFormat($tenant_service->cost);
            })
            ->editColumn('maintenance_cost', function ($tenant_service) {
                return numberFormat($tenant_service->maintenance_cost);
            })
            ->addColumn('actions', function ($tenant_service) {
                return $tenant_service->action_buttons;
            })
            ->rawColumns(['actions','package'])
            ->make(true);
    }
}
