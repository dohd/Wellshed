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

namespace App\Http\Controllers\Focus\tenant_deposit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TenantDepositsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $controller;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(TenantDepositsController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * 
     */
    public function __invoke(Request $request)
    {
        $core = $this->controller->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('tid', function ($deposit) {
                return '<a href="#"><b>'. gen4tid('PMT-', $deposit->tid)  .'</b></a>';
            })
            ->editColumn('date', function ($deposit) {
                return date('d-M-Y', strtotime($deposit->date));
            })
            ->editColumn('amount', function ($deposit) {
                return '<span><b>'.numberFormat($deposit->amount).'</b></span>';
            })
            ->make(true);
    }
}
