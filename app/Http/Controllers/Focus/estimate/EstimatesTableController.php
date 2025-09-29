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
namespace App\Http\Controllers\Focus\estimate;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\estimate\EstimateRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class EstimatesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var EstimateRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param EstimateRepository $repository ;
     */
    public function __construct(EstimateRepository $repository)
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
            ->editColumn('tid', function ($estimate) {
                return gen4tid('EST-', $estimate->tid);
            })
            ->editColumn('date', function ($estimate) {
                return dateFormat($estimate->date);
            })
            ->addColumn('customer', function ($estimate) {
                return @$estimate->customer->company ?: @$estimate->customer->name;
            })
            ->addColumn('quote_tid', function ($estimate) {
                $quote = $estimate->quote;
                if ($quote) return gen4tid($quote->bank_id? 'PI-' : 'QT', $quote->tid);
            })
            ->editColumn('est_total', function ($estimate) {
                return numberFormat($estimate->est_total);
            })
            ->editColumn('balance', function ($estimate) {
                return numberFormat($estimate->balance);
            })
            ->addColumn('actions', function ($estimate) {
                $params = [
                    'estimate_id' => $estimate->id,
                    'selected_products' => $estimate->quote_id, 
                    'customer' => $estimate->customer_id, 
                    'customer_id' => $estimate->customer_id
                ];
                $inv_btn = '<a href="'. route('biller.invoices.filter_invoice_quotes', $params) .'" class="btn btn-purple round" data-toggle="tooltip" data-placement="bottom" title="Invoice"><i class="fa fa-usd" aria-hidden="true"></i></a>';
                if ($estimate->invoice) $inv_btn = '';
                return $inv_btn . ' ' . $estimate->action_buttons;
            })
            ->make(true);
    }
}
