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
namespace App\Http\Controllers\Focus\stock_adj;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\stock_adj\StockAdjRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class StockAdjsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var StockAdjRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param StockAdjRepository $repository ;
     */
    public function __construct(StockAdjRepository $repository)
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
            ->editColumn('date', function ($stock_adj) {
                return dateFormat($stock_adj->date);
            })
            ->editColumn('approval_status', function ($stock_adj) {
                if ($stock_adj->approval_status == 'Approved')
                    return '<span class="badge badge-primary">'. $stock_adj->approval_status .'</span>';
                return '<span class="badge badge-secondary">'. $stock_adj->approval_status .'</span>';
            })
            ->addColumn('account', function ($stock_adj) {
                return @$stock_adj->account->holder;
            })
            ->editColumn('total', function ($stock_adj) {
                return numberFormat($stock_adj->total);
            })
            ->addColumn('actions', function ($stock_adj) {
                return $stock_adj->action_buttons;
            })
            ->make(true);
    }
}
