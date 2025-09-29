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
namespace App\Http\Controllers\Focus\stock_rcv;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\stock_rcv\StockRcvRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class StockRcvsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var StockRcvRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param StockRcvRepository $repository ;
     */
    public function __construct(StockRcvRepository $repository)
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
            ->editColumn('date', function ($stock_rcv) {
                return dateFormat($stock_rcv->date);
            }) 
            ->addColumn('transf_tid', function ($stock_rcv) {
                return gen4tid('STR-', $stock_rcv->stock_transfer->tid);
            })
            ->addColumn('receiver', function ($stock_rcv) {
                return @$stock_rcv->receiver->full_name;
            })
            ->addColumn('actions', function ($stock_rcv) {
                return $stock_rcv->action_buttons;
            })
            ->make(true);
    }
}
