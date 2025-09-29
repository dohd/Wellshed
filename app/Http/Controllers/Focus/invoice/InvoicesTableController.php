<?php

namespace App\Http\Controllers\Focus\invoice;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\invoice\InvoiceRepository;
use App\Http\Requests\Focus\invoice\ManageInvoiceRequest;

/**
 * Class InvoicesTableController.
 */
class InvoicesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var InvoiceRepository
     */
    protected $invoice;

    /**
     * contructor to initialize repository object
     * @param InvoiceRepository $invoice ;
     */
    public function __construct(InvoiceRepository $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * This method return the data of the model
     * @param ManageInvoiceRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageInvoiceRequest $request)
    {
        $prefixes = prefixesArray(['invoice', 'quote', 'proforma_invoice'], auth()->user()->ins);
        $core = $this->invoice->getForDataTable();
        // aggregate
        $sum1 = (clone $core)->where('fx_curr_rate', '<=', 1)->selectRaw('SUM(total) as total, SUM(total-amountpaid) as balance')->first();
        $sum2 = (clone $core)->where('fx_curr_rate', '>', 1)->selectRaw('SUM(total*fx_curr_rate) as total, SUM((total-amountpaid)*fx_curr_rate) as balance')->first();
        $aggregate = [
            'amount_total' => numberFormat(@$sum1['total'] + @$sum2['total']),
            'balance_total' => numberFormat(@$sum1['balance'] + @$sum2['balance']),
        ];       

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('customer', function ($invoice) {
                $link = '';
                $customer = $invoice->customer;
                if ($customer) $link = ' <a class="font-weight-bold" href="'. route('biller.customers.show', $customer) .'">'. ($customer->company ?: $customer->name) .'</a>'; 
                return $link;             
            })
            ->addColumn('tid', function ($invoice) use($prefixes) {
                return '<a class="font-weight-bold" href="'.route('biller.invoices.show', [$invoice->id]).'">' 
                    . gen4tid("{$prefixes[0]}-", $invoice->tid) .'</a>';
            })
            ->addColumn('invoicedate', function ($invoice) {
                return dateFormat($invoice->invoicedate);
            })
            ->addColumn('ledgerAccount', function ($invoice) {
                return @$invoice->ledgerAccount->holder ?: '<b> <i>Uncategorized Income</i> </b>';
            })
            ->addColumn('subtotal', function ($invoice) {
                return numberFormat($invoice->subtotal);
            })
            ->addColumn('tax', function ($invoice) {
                return numberFormat($invoice->tax);
            })
            ->addColumn('total', function ($invoice) {
                return numberFormat($invoice->total);
            })
            ->addColumn('balance', function ($invoice) {
                return numberFormat($invoice->total - $invoice->amountpaid);
            })
            ->addColumn('status', function ($invoice) {
                return '<span class="st-' . $invoice->status . '">' . trans('payments.' . $invoice->status) . '</span>';
            })
            ->addColumn('invoiceduedate', function ($invoice) {
                return dateFormat($invoice->invoiceduedate);
            })
            ->addColumn('quote_tid', function ($invoice) use($prefixes) {
                $links = [];
                foreach ($invoice->products as $item) {
                    $quote = $item->quote;
                    if ($quote) {
                        $tid = gen4tid($quote->bank_id ? "{$prefixes[2]}-" : "{$prefixes[1]}-", $quote->tid);
                        $links[] = '<a href="'. route('biller.quotes.show', $quote) .'">'. $tid .'</a>';
                    }
                }
                return implode(', ', array_unique($links));
            })
            ->addColumn('last_pmt', function ($invoice) {
                $last_pmt = '';
                if ($invoice->payments()->exists()) {
                    $last_pmt_item = $invoice->payments()->latest()->first();
                    if (@$last_pmt_item->paid_invoice) $last_pmt .= dateFormat($last_pmt_item->paid_invoice->date);
                } 
                return $last_pmt;
            })
            ->addColumn('aggregate', function ($invoice) use($aggregate) {
                return $aggregate;
            })
            ->addColumn('actions', function ($invoice) {
                return $invoice->action_buttons;
            })
            ->make(true);
    }
}
