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
namespace App\Http\Controllers\Focus\job_valuation;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\job_valuation\JobValuationRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class JobValuationsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var repository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param JobValuationRepository $repository ;
     */
    public function __construct(JobValuationRepository $repository)
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
            ->addColumn('row_check', function ($job_valuation) {
                if ($job_valuation->invoice)
                    return  '<input checked disabled type="checkbox"  class="row-check ml-1" href="'. route('biller.invoices.filter_invoice_quotes', ['job_valuation_id' => $job_valuation->id]) .'">';
                return  '<input type="checkbox"  class="row-check ml-1" href="'. route('biller.invoices.filter_invoice_quotes', ['job_valuation_id' => $job_valuation->id]) .'">';
            })
            ->addColumn('invoice_tid', function ($job_valuation) {
                $invoice = $job_valuation->invoice;
                if (!$invoice) return;
                return '<a href="'. route('biller.invoices.show', $invoice) .'">'. gen4tid('INV-', $invoice->tid) .'</a>';
            })
            ->editColumn('tid', function ($job_valuation) {
                return gen4tid('JV-', $job_valuation->tid);
            })
            ->addColumn('quote_tid', function ($job_valuation) {
                $quote = $job_valuation->quote;
                if (!$quote) return;
                return '<a href="'. route('biller.quotes.show', $quote) .'">'. gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) .'</a>';
            })
            ->addColumn('quote_amount', function ($job_valuation) {
                $quote = $job_valuation->quote;
                if (!$quote) return;
                return numberFormat($quote->subtotal);
            })
            ->editColumn('date', function ($job_valuation) {
                return dateFormat($job_valuation->date);
            }) 
            ->addColumn('customer', function ($job_valuation) {
                $customer = '';
                if ($job_valuation->customer) $customer = $job_valuation->customer->company ?: $job_valuation->customer->name;
                if ($customer && $job_valuation->branch) $customer .= " - {$job_valuation->branch->name}";
                return $customer;
            })
            ->editColumn('subtotal', function ($job_valuation) {
                return numberFormat($job_valuation->valued_subtotal);
            })
            ->editColumn('balance', function ($job_valuation) {
                return numberFormat($job_valuation->balance);
            })
            ->addColumn('actions', function ($job_valuation) {
                return $job_valuation->action_buttons;
            })
            ->make(true);
    }
}
