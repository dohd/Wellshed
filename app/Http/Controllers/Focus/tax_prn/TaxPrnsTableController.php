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
namespace App\Http\Controllers\Focus\tax_prn;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\tax_prn\TaxPrnRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class TaxPrnsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaxPrnRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param TaxPrnRepository $repository ;
     */
    public function __construct(TaxPrnRepository $repository)
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
        $core = $this->repository->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->editColumn('amount', function ($tax_prn) {
                return numberFormat($tax_prn->amount);
            })
            ->editColumn('period_from', function ($tax_prn) {
                return dateFormat($tax_prn->period_from);
            })
            ->editColumn('period_to', function ($tax_prn) {
                return dateFormat($tax_prn->period_to);
            })
            ->editColumn('ackn_date', function ($tax_prn) {
                return dateFormat($tax_prn->ackn_date);
            })
            ->addColumn('actions', function ($tax_prn) {
                return $tax_prn->action_buttons;
            })
            ->make(true);
    }
}
