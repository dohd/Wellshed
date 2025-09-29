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
namespace App\Http\Controllers\Focus\tax_report;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\tax_report\TaxReportRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class TaxReportsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaxReportRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param TaxReportRepository $repository ;
     */
    public function __construct(TaxReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $query = $this->repository->getForDataTable();

        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()   
            ->addColumn('tax_group', function ($report) {
                $tax_group = '';
                if ($report->sale_subtotal > 0 && $report->tax_group == 0) {
                    $tax_group = 'Exempted Rated';
                } elseif ($report->purchase_subtotal > 0 && $report->tax_group == 0) {
                    $tax_group = 'Zero Rated (0%)';
                } elseif ($report->tax_group == 8) {
                    $tax_group = 'Other Rated (8%)';
                } elseif ($report->tax_group == 16) {
                    $tax_group = 'General Rated (16%)';
                }
                return "{$tax_group} Sales/Purchases";
            }) 
            ->addColumn('sale_tax', function ($report) {
                return numberFormat($report->sale_tax);
            })
            ->addColumn('purchase_tax', function ($report) {
                return numberFormat($report->purchase_tax);
            })
            ->editColumn('created_at', function ($report) {
                return dateFormat($report->created_at);
            })
            ->orderColumn('created_at', '-created_at $1')
            ->addColumn('return_no', function ($report) {
                return @$report->tax_prn->return_no;
            })
            ->addColumn('actions', function ($report) {
                return $report->action_buttons;
            })
            ->make(true);
    }
}
