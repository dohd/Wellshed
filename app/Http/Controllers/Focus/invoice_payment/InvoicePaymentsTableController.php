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
namespace App\Http\Controllers\Focus\invoice_payment;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\invoice\InvoiceRepository;
use App\Http\Requests\Focus\invoice\ManageInvoiceRequest;
use App\Repositories\Focus\invoice_payment\InvoicePaymentRepository;

/**
 * Class InvoicesTableController.
 */
class InvoicePaymentsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var InvoiceRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param InvoiceRepository $repository ;
     */
    public function __construct(InvoicePaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param ManageInvoiceRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageInvoiceRequest $request)
    {
        $core = $this->repository->getForDataTable();
        $prefixes = prefixesArray(['invoice'], auth()->user()->ins);
        
        // aggregate
        $amount_total = $core->sum('amount');
        $unallocated_total = $amount_total - $core->sum('allocate_ttl');
        $aggregate = [
            'amount_total' => numberFormat($amount_total),
            'unallocated_total' => numberFormat($unallocated_total),
        ];

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('tid', function ($payment) {
                return gen4tid('PMT-', $payment->tid);
            })
            ->editColumn('customer_id', function ($payment) {
                if (!$payment->customer) return '';
                $company = @$payment->customer->company;
                $name = @$payment->customer->name;
                if ($company && $name) $name = "{$company} - {$name}";
                $name = $company ?: $name;
                return '<a href="'. route('biller.customers.show', $payment->customer) .'">'. $name .'</a>';
            })
            ->addColumn('account', function ($payment) {
                if ($payment->account)
                return $payment->account->holder;
            })        
            ->addColumn('date', function ($payment) {
                return dateFormat($payment->date);
            })
            ->addColumn('amount', function ($payment) {
                return numberFormat($payment->amount);
            })
            ->addColumn('unallocated', function ($payment) {
                return numberFormat($payment->amount - $payment->allocate_ttl);
            })
            ->addColumn('payment_mode', function ($payment) {
                $pmt_type = ucfirst(str_replace('_', ' ', $payment->payment_type));
                return "{$payment->payment_mode} - {$payment->reference} <br> ({$pmt_type})";
            })
            ->addColumn('invoice_tid', function ($payment) use($prefixes) {
                $invoice_tids = array();
                foreach ($payment->items as $item) {
                    if ($item->invoice) $invoice_tids[] = '<a href="'. route('biller.invoices.show', $item->invoice) .'">'. gen4tid("{$prefixes[0]}-", $item->invoice->tid) .'</a>';
                }
                return implode(', ', $invoice_tids);
            })
            ->addColumn('aggregate', function ($payment) use($aggregate) {
                return $aggregate;
            })
            ->addColumn('actions', function ($payment) {
                return ' <a href="' . route('biller.invoices.print_payment', $payment) . '" target="_blank"  class="btn btn-purple round"><i class="fa fa-print"></i></a> '
                    . $payment->action_buttons;

            })
            ->make(true);
    }
}
