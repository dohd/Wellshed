<?php

namespace App\Http\Controllers\Focus\customer;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\customer\CustomerRepository;
use App\Http\Requests\Focus\customer\ManageCustomerRequest;
use Illuminate\Support\Facades\Request;

/**
 * Class CustomersTableController.
 */
class CustomersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var CustomerRepository
     */
    protected $customer;
    protected $balance = 0;

    /**
     * contructor to initialize repository object
     * @param CustomerRepository $customer ;
     */
    public function __construct(CustomerRepository $customer)
    {
        $this->customer = $customer;
    }

    /**
     * This method return the data of the model
     * @param ManageCustomerRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        if (request('is_transaction')) return $this->invoke_transaction();
        if (request('is_invoice')) return $this->invoke_invoice();
        if (request('is_statement')) return $this->invoke_statement();
            
        $core = $this->customer->getForDataTable();
        // foreach ($core as $key => $customer) {
        //     $customer->update(['tid' => $key+1]);
        // }

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function($customer) {
                return gen4tid('CRM-', $customer->tid);
            })
            ->editColumn('name', function($customer) {
                $customerName = $customer->name; 
                return '<a class="font-weight-bold" href="' . route('biller.customers.show', $customer) . '">' . $customerName . '</a>';
            })
            ->addColumn('company', function ($customer) {
                $company = $customer->company;                
                return '<a class="font-weight-bold" href="' . route('biller.customers.show', $customer) . '">' . $company . '</a>';
            })
            ->addColumn('balance', function ($customer) {
                $bal = $customer->paymentReceipts->sum(fn($v) => $v->debit - $v->credit);
                return numberFormat($bal);
            })
            ->addColumn('actions', function ($customer) {
                $customer->action_buttons;
            })
            ->make(true);
    }

    /**
     * Statement on Account Data
     */
    public function invoke_transaction()
    {
        $core = $this->customer->getTransactionsForDataTable();
        $core = $core->sortBy('tr_date');

        // filter by project
        if (request('project_id')) {
            $core = $core->whereIn('tr_type', ['inv', 'dep', 'genjr'])
                ->filter(function($tr) {
                    $project = null;
                    $deposit = $tr->deposit;
                    $invoice = $tr->invoice;
                    if ($deposit) {
                        $project = $deposit->project;
                        if (!$project) {
                            // check for project in invoices
                            foreach ($deposit->invoices as $invoice) {
                                if (@$invoice->project->id == request('project_id')) {
                                    $project = $invoice->project;
                                    break;
                                }
                                else {
                                    foreach ($invoice->quotes as $quote) {
                                        if (@$quote->project->id == request('project_id')) {
                                            $project = $quote->project;
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    elseif ($invoice) {
                        $project = $invoice->project;
                        if (!$project) {
                            foreach ($invoice->quotes as $quote) {
                                if ($quote->project->id == request('project_id')) {
                                    $project = $quote->project;
                                    break;
                                }
                            }
                        }                        
                    }
                    elseif ($tr->manualjournal) $project = @$tr->manualjournal->paid_invoice->project;

                    // if (@$tr->invoice->tid == 1513) dd($tr->invoice);
                    if (@$project->id == request('project_id')) {
                        return true;
                    }
                });
        }

        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('date', function ($tr) {
            $date = dateFormat($tr->tr_date);
            $sort_id = strtotime($date);
            return "<span sort_id='{$sort_id}'>{$date}</span>";
        })
        ->addColumn('type', function ($tr) {
            return $tr->tr_type;
        })
        ->addColumn('note', function ($tr) {
            $note = $tr->note;
            // invoice
            if ($tr->tr_type == 'inv' && $tr->invoice) {
                $tid = gen4tid('Inv-', $tr->invoice->tid);
                $invNote = $tr->invoice->notes;
                $note = $invNote? "({$tid}) - {$invNote}" : $tid;
            }
            // deposit
            if ($tr->tr_type == 'dep' && $tr->deposit) {
                $project = @$tr->deposit->project;
                if ($project) {
                    $tid = gen4tid('PRJ-', $project->tid);
                    $name = $project->name;
                    $note = "({$tid}) - {$name} <br> {$note}";
                }
            }
            return $note;
        })
        ->addColumn('invoice_amount', function ($tr) {
            return numberFormat($tr->debit);
        })
        ->addColumn('amount_paid', function ($tr) {
            return numberFormat($tr->credit);
        })
        ->addColumn('account_balance', function ($tr) {
            if ($tr->debit > 0) $this->balance += $tr->debit;
            elseif ($tr->credit > 0) $this->balance -= $tr->credit;

            return numberFormat($this->balance);
        })
        ->make(true);
    }

    /**
     * Invoices Data
     */
    public function invoke_invoice()
    {
        $core = $this->customer->getInvoicesForDataTable();

        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('date', function ($invoice) {
            return dateFormat($invoice->invoicedate);
        })
        ->addColumn('status', function ($invoice) {
            return $invoice->status;
        })
        ->addColumn('note', function ($invoice) {
            return gen4tid('Inv-', $invoice->tid) . ' - ' . $invoice->notes;
        })
        ->addColumn('amount', function ($invoice) {
            return numberFormat($invoice->total);
        })
        ->addColumn('paid', function ($invoice) {
            return numberFormat($invoice->amountpaid);
        })
        ->make(true);
    }

    /**
     * Statement on Invoice Data
     */
    public function invoke_statement()
    {
        $core = $this->customer->getStatementForDataTable();
        if (request('project2_id')) {
            $core = $core->where('project_id', request('project2_id'))->all();
        }

        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('date', function ($statement) {
            return dateFormat($statement->date);
        })
        ->addColumn('type', function ($statement) {
            $type = $statement->type;
            switch ($type) {
                case 'invoice': 
                    $type = '<a href="'. route('biller.invoices.show', $statement->invoice_id) .'">'. $type .'</a>';
                    break;
                case 'payment': 
                    $type = '<a href="'. route('biller.invoice_payments.show', $statement->payment_id) .'">'. $type .'</a>';
                    break;
                case 'credit-note': 
                    $type = '<a href="'. route('biller.creditnotes.show', $statement->creditnote_id) .'">'. $type .'</a>';
                    break;
                case 'debit-note': 
                    $type = '<a href="'. route('biller.creditnotes.show', [$statement->debitnote_id, 'is_debit=1']) .'">'. $type .'</a>';
                    break;
                case 'withholding': 
                    $type = '<a href="'. route('biller.withholdings.show', $statement->withholding_id) .'">'. $type .'</a>';
                    break;    
            }
            
            return $type;
        })
        ->addColumn('note', function ($statement) {
            return $statement->note;
        })
        ->addColumn('invoice_amount', function ($statement) {
            return numberFormat($statement->debit);
        })
        ->addColumn('amount_paid', function ($statement) {
            return numberFormat($statement->credit);
        })
        ->addColumn('invoice_balance', function ($statement) {
            if ($statement->type == 'invoice') 
                $this->balance = $statement->debit;
            else $this->balance -= $statement->credit;

            return numberFormat($this->balance);
        })
        ->make(true);
    }
}
