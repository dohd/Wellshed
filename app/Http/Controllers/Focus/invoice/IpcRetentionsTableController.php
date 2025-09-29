<?php

namespace App\Http\Controllers\Focus\invoice;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\invoice\InvoiceRepository;
use App\Http\Requests\Focus\invoice\ManageInvoiceRequest;
use App\Models\boq_valuation\BoQValuation;
use App\Models\job_valuation\JobValuation;

class IpcRetentionsTableController extends Controller
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
            ->addColumn('date', function ($model) { 
                return dateFormat($model->date);
            })
            ->addColumn('customer', function ($model) {
                $customer = $model->customer;
                $link = ' <a class="font-weight-bold" href="'. route('biller.customers.show', $customer) .'">'. ($customer->company ?: $customer->name) .'</a>'; 
                return $link;             
            })
            ->addColumn('tid', function ($model) {
                if ($model->type == 'quote') {
                    return '<a class="font-weight-bold" href="'.route('biller.job_valuations.show', $model).'">' . gen4tid("JV-", $model->tid) .'</a>';
                } elseif ($model->type == 'boq') {
                    return '<a class="font-weight-bold" href="'.route('biller.boq_valuations.show', $model).'">' . gen4tid("BQV-", $model->tid) .'</a>';
                }                 
            })
            ->addColumn('invoice_tid', function ($model) { 
                if ($model->invoice) {
                    return '<a class="font-weight-bold" href="'.route('biller.invoices.show', $model->invoice).'">' . gen4tid("INV-", $model->invoice->tid) .'</a>';                 
                }
            })
            ->addColumn('invoicedate', function ($model) { 
                if ($model->invoice) {
                    return dateFormat($model->invoice->invoicedate);                    
                }
            })
            ->addColumn('invoiceduedate', function ($model) {
                if ($model->invoice) {
                    return dateFormat($model->invoice->invoiceduedate);                    
                }
            })
            ->addColumn('quote_boq_tid', function ($model) {
                if ($model->type == 'quote' && $model->quote) {
                    return '<a class="font-weight-bold" href="'.route('biller.quotes.show', $model->quote).'">' . gen4tid($model->quote->bank_id? "PI-" : "QT-", $model->quote->tid) .'</a>';
                } elseif ($model->type == 'boq' && $model->boq) {
                    return '<a class="font-weight-bold" href="'.route('biller.boqs.show', $model->boq).'">' . gen4tid("BOQ-", $model->boq->tid) .'</a>';
                }
            })
            ->addColumn('project_tid', function ($model) {
                $project = null;
                if ($model->type == 'quote' && $model->quote) {
                    $project = @$model->quote->project;
                } elseif ($model->type == 'boq') {
                    $project = $model->project;
                }
                 
                if ($project) {
                    return '<a class="font-weight-bold" href="'.route('biller.projects.show', $project).'">' . gen4tid("PRJ-", $project->tid) .'</a>';
                }
            })
            ->addColumn('project_end', function ($model) {
                if ($model->completion_date) {
                    return dateFormat($model->completion_date);
                }
            })
            ->addColumn('dlp', function ($model) {
                return +$model->dlp_period;
            })
            ->addColumn('dlp_end', function ($model) {
                if ($model->completion_date) {
                    $m = (int) $model->dlp_period;
                    return date('Y-m-d', strtotime("{$model->completion_date} +{$m} months"));
                }
            })
            ->addColumn('retained_amount', function ($model) {
                return numberFormat($model->retention);
            })
            ->addColumn('retained_pcg', function ($model) {
                return +$model->perc_retention;
            })  
            ->addColumn('status', function ($model) { 
                $status = '<span class="badge bg-warning">Open</span>';
                if ($model->invoice) {
                    $status = '<span class="badge bg-success">Closed</span>';
                }
                return $status;
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
        // job valuations
        $jobValuations = JobValuation::where('retention', '>', 0)
        ->when(request('customer_id'), function($q) {
            $q->where('customer_id', request('customer_id'));
        })
        ->when(request('project_id'), function($q) {
            $q->whereHas('quote', function($q) {
                $q->whereHas('project', fn($q) => $q->where('projects.id', request('project_id')));
            });
        })
        ->with([
            'customer' => fn($q) => $q->select('id', 'company', 'name'),
            'branch' => fn($q) => $q->select('id', 'name'),
            'quote' => fn($q) => $q->select('id', 'bank_id', 'tid'),
            'invoice' => fn($q) => $q->select('id', 'job_valuation_id', 'tid', 'invoicedate'),
        ])
        ->get(['id', 'customer_id', 'quote_id', 'branch_id', 'tid', 'date', 'retention', 'perc_retention'])
        ->map(function($item) {
            $item->fill([
                'type' => 'quote',
            ]);
            return $item;
        });

        // boq valuations
        $boqValuations = BoQValuation::where('retention', '>', 0)
        ->when(request('customer_id'), function($q) {
            $q->where('customer_id', request('customer_id'));
        })
        ->when(request('project_id'), function($q) {
            $q->whereHas('project')->where('project_id', request('project_id'));
        })
        ->with([
            'customer' => fn($q) => $q->select('id', 'company', 'name'),
            'branch' => fn($q) => $q->select('id', 'name'),
            'quote' => fn($q) => $q->select('id', 'bank_id', 'tid'),
            'invoice' => fn($q) => $q->select('id', 'job_valuation_id', 'tid', 'invoicedate'),
        ])
        ->get(['id', 'customer_id', 'quote_id', 'branch_id', 'tid', 'date', 'retention', 'perc_retention'])
        ->map(function($item) {
            $item->fill([
                'type' => 'boq',
            ]);
            return $item;
        });

        return $jobValuations->merge($boqValuations)->sortByDesc('date');        
    }
}
