<?php

namespace App\Http\Controllers\Focus\invoice;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\invoice\InvoiceRepository;
use App\Http\Requests\Focus\invoice\ManageInvoiceRequest;
use App\Models\creditnote\CreditNote;
use App\Models\invoice\Invoice;
use DB;

class SalesVarianceTableController extends Controller
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
        $core = $this->getForDataTable();   
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('customer', function ($model) {
                $customer = $model->customer;
                $link = ' <a class="font-weight-bold" href="'. route('biller.customers.show', $customer) .'">'. ($customer->company ?: $customer->name) .'</a>'; 
                return $link;             
            })
            ->addColumn('tid', function ($model) {
                if ($model->type == 'invoice') {
                    return '<a class="font-weight-bold" href="'.route('biller.invoices.show', $model).'">' . gen4tid("INV-", $model->tid) .'</a>';
                } elseif ($model->type == 'creditnote') {
                    return '<a class="font-weight-bold" href="'.route('biller.creditnotes.show', $model).'">' . gen4tid("CN-", $model->tid) .'</a>';
                }                    
            })
            ->addColumn('invoicedate', function ($model) {
                return dateFormat($model->invoicedate);
            })
            ->addColumn('invoiceduedate', function ($model) {
                return dateFormat($model->invoiceduedate);
            })
            ->addColumn('account', function ($model) {
                if ($model->type == 'invoice') {
                    return @$model->ledgerAccount->holder ?: '<b> <i>Uncategorized Income</i> </b>';
                } elseif ($model->type == 'creditnote') {
                   return @$model->invoice->ledgerAccount->holder ?: '<b> <i>Uncategorized Income</i> </b>';
                }   
            })
            ->addColumn('subtotal', function ($model) {
                if ($model->fx_total > 0) {
                    return numberFormat($model->fx_subtotal);
                }
                return numberFormat($model->subtotal);
            })
            ->addColumn('tax', function ($model) {
                if ($model->fx_total > 0) {
                    return numberFormat($model->fx_tax);
                }
                return numberFormat($model->tax);
            })
            ->addColumn('total', function ($model) {
                if ($model->fx_total > 0) {
                    return numberFormat($model->fx_total);
                }
                return numberFormat($model->total);
            })         
            ->addColumn('fx_total', function ($model) {
                if ($model->fx_total > 0 && $model->currency_id) {
                    return amountFormat($model->total, $model->currency_id);
                }
            })         
            ->make(true);
    }

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        // invoices
        $invoices = Invoice::whereHas('customer')
            ->when(request('start_date') && request('end_date'), function ($q) {
                $q->whereBetween('invoicedate', [
                    date_for_database(request('start_date')), 
                    date_for_database(request('end_date'))
                ]);
            })
            ->when(request('customer_id'), function($q) {
                $q->where('customer_id', request('customer_id'));
            })
            ->when(request('invoice_category'), function($q) {
                $q->where('account_id', request('invoice_category'));
            })
            ->when(request('project_id'), function($q) {
                $q->where(function($q) {
                    $q->whereHas('quotes', function($q) {
                        $q->whereHas('project', function($q) {
                            $q->where('projects.id', request('project_id'));
                        });
                    });
                    $q->orWhereHas('project', fn($q) => $q->where('projects.id', request('project_id')));
                    $q->orWhereHas('boqValuation.project', fn($q) => $q->where('projects.id', request('project_id')));
                });
            })
            ->with([
                'customer' => fn($q) => $q->select('id', 'company', 'name'),
                'ledgerAccount' => fn($q) => $q->select('id', 'holder'),
            ])
            ->get([
                'id', 'customer_id', 'account_id', 'currency_id', 'tid', 'invoicedate', 'invoiceduedate', 'notes', 
                'subtotal', 'tax', 'total', 'fx_curr_rate', 'fx_total', 'fx_subtotal', 'fx_tax'
            ])
            ->map(function ($item) {
                $item->type = 'invoice';
                $item->date = $item->invoicedate;
                return $item;
            });

        // credit notes
        $creditNotes = CreditNote::whereHas('customer')
            ->when(request('start_date') && request('end_date'), function ($q) {
                $q->whereBetween('date', [
                    date_for_database(request('start_date')), 
                    date_for_database(request('end_date'))
                ]);
            })
             ->when(request('customer_id'), function($q) {
                $q->where('customer_id', request('customer_id'));
            })
            ->when(request('invoice_category'), function($q) {
                $q->where('account_id', request('invoice_category'));
            })
            ->with([
                'invoice',
                'customer' => fn($q) => $q->select('id', 'company', 'name'),
                'ledgerAccount' => fn($q) => $q->select('id', 'holder'),
            ])
            ->get([
                'id', 'customer_id', 'account_id', 'currency_id', 'invoice_id', 'tid', 'date', 'note',
                'subtotal', 'tax', 'total', 'fx_curr_rate', 'fx_total', 'fx_subtotal', 'fx_tax'
            ])
            ->map(function ($item) {
                $item->fill([
                    'type' => 'creditnote',
                    'invoicedate' => $item->date,
                    'invoiceduedate' => $item->date,
                    'notes' => $item->note,
                    'subtotal' => -$item->subtotal,
                    'tax' => -$item->tax,
                    'total' => -$item->total,
                    'fx_subtotal' => -$item->fx_subtotal,
                    'fx_tax' => -$item->fx_tax,
                    'fx_total' => -$item->fx_total,
                ]);
                return $item;
            });

        return $invoices->merge($creditNotes)->sortByDesc('invoicedate');        
    }
}
