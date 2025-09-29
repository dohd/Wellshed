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

namespace App\Http\Controllers\Focus\tenant_invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TenantInvoicesTableController extends Controller
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
    public function __construct(TenantInvoicesController $controller)
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
            ->editColumn('tid', function ($invoice) {
                return '<a href="#"><b>'. gen4tid('INV-', $invoice->tid) .'</b></a>';
            })
            ->editColumn('invoicedate', function ($invoice) {
                return date('d-M-Y', strtotime($invoice->invoicedate));
            })
            ->editColumn('invoiceduedate', function ($invoice) {
                return date('d-M-Y', strtotime($invoice->invoiceduedate));
            })
            ->editColumn('total', function ($invoice) {
                return '<span><b>'.numberFormat($invoice->total).'</b></span>';
            })
            ->editColumn('status', function ($tenant) {
                $variant = 'badge-secondary';
                if ($tenant->status == 'paid') $variant = 'badge-success';
                if ($tenant->status == 'partial') $variant = 'badge-warning';
                return '<span class="badge '. $variant .'">'. $tenant->status .'</span>';
            })
            ->make(true);
    }
}
