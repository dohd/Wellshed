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
namespace App\Http\Controllers\Focus\sale_return;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\sale_return\SaleReturnRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class SaleReturnsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var SaleReturnRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param SaleReturnRepository $repository ;
     */
    public function __construct(SaleReturnRepository $repository)
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
            ->editColumn('tid', function ($sale_return) {
                return gen4tid('SR-', $sale_return->tid);
            })
            ->editColumn('date', function ($sale_return) {
                return dateFormat($sale_return->date);
            }) 
            ->addColumn('customer', function ($sale_return) {
                $customer = '';
                if ($sale_return->customer) $customer = $sale_return->customer->company ?: $sale_return->customer->name;
                return $customer;
            })
            ->addColumn('invoice_tid', function ($sale_return) {
                $tid = @$sale_return->invoice->tid;
                if ($tid) $tid = gen4tid('INV-', $tid);
                return $tid;
            })
            ->addColumn('quote_tid', function ($sale_return) {
                $tid = @$sale_return->quote->tid;
                $bank_id = @$sale_return->quote->bank_id;
                if ($tid) $tid = gen4tid($bank_id? 'PI-' : 'QT-', $tid);
                return $tid;
            })
            ->editColumn('total', function ($sale_return) {
                return numberFormat($sale_return->total);
            })
            ->addColumn('actions', function ($sale_return) {
                return $sale_return->action_buttons;
            })
            ->make(true);
    }
}
