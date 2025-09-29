<?php

namespace App\Http\Controllers\Focus\account;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\dailyBusinessMetrics\DailyBusinessMetricController;
use App\Repositories\Focus\account\AccountRepository;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ProjectGrossProfitTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $repository;

    // income, expense, profit
    protected $income = 0;
    protected $expense = 0;
    protected $profit = 0;
    protected $total_profit = 0;
    protected $invoices = [];

    /**
     * contructor to initialize repository object
     * @param AccountRepository $repository ;
     */
    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->repository->getForProjectGrossProfit();

        $projectLedgerTotals = function($project) {
            $totals = [
                'wip' => 0,
                'labour' => 0,
                'material' => 0,
                'general' => 0,
                'transport' => 0,
            ];
            $transactions = $project->transactions;
            foreach ($transactions as $txn) {
                $system = @$txn->account->account_type_detail->system;
                $debitBal = $txn->debit - $txn->credit;
                switch ($system) {
                    case 'work_in_progress':
                        $totals['wip'] += $debitBal;
                        break;
                    case 'cog_labour':
                        $totals['labour'] += $debitBal;
                        break;
                    case 'cog_material':
                        $totals['material'] += $debitBal;
                        break;
                    case 'cost_of_goods_sold':
                        $totals['general'] += $debitBal;
                        break;
                    case 'shipping_freight':
                        $totals['transport'] += $debitBal;
                        break;
                }
            }
        
            return $totals;
        };
    
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('customer', function($project) {
                $customer = '';
                if ($project->customer_project) {
                    $customer = $project->customer_project->company;
                    if ($project->branch) $customer .= " - {$project->branch->name}";
                }
                return $customer;
            })
            ->addColumn('tid', function($project) {
                return '<a href="'. route('biller.projects.show', $project) .'">'. gen4tid('Prj-', $project->tid) .'</a>';
            })
            ->addColumn('status', function($project) {
                return 'Active';
            })
            ->addColumn('quote_amount', function($project) {
                $quotes = '';
                foreach ($project->quotes as $quote) {
                    $tid = gen4tid($quote->bank_id? 'PI-': 'QT-', $quote->tid);
                    $quotes .= '<a href="'. route('biller.quotes.show', $quote->id) .'">'. $tid .'</a>' . ' : ' . numberFormat($quote->subtotal) . '<br>';
                }
                return $quotes;
            })
            ->addColumn('verify_date', function($project) {
                $dates = [];
                foreach ($project->quotes as $quote) {
                    if ($quote->verified_amount > 0) {
                        $dates[] = dateFormat($quote->verification_date);
                    }
                }
                return implode('<br>', $dates);
            })            
            ->addColumn('income', function($project) {
                // initialize non-quote invoices
                $invoices = collect()->merge($project->invoices); 
                $netTotal = 0;
                // quote invoices
                $invoiceIds = [];
                foreach ($project->quotes as $quote) {
                    if ($quote->invoice && !in_array($quote->invoice->id, $invoiceIds)) {
                        $invoiceIds[] = $quote->invoice->id;
                        $invoices->add($quote->invoice);
                    }
                }
                $this->invoices = $invoices;

                foreach ($invoices as $invoice) {
                    // sum up invoices
                    $isFx = +$invoice->fx_curr_rate && +$invoice->fx_curr_rate != 1;
                    if ($isFx) $netTotal += floatval($invoice->fx_subtotal);
                    else $netTotal += floatval($invoice->subtotal);
                    // deduct credit notes
                    if (count($invoice->creditnotes)) {
                        foreach ($invoice->creditnotes as $cnote) {
                            $isFx = +$cnote->fx_curr_rate && +$cnote->fx_curr_rate != 1;
                            if ($isFx) $netTotal -= floatval($cnote->fx_subtotal);
                            else $netTotal -= floatval($cnote->subtotal);
                        }
                    }
                    // add debit notes
                    if (count($invoice->debitnotes)) {
                        foreach ($invoice->debitnotes as $dnote) {
                            $isFx = +$dnote->fx_curr_rate && +$dnote->fx_curr_rate != 1;
                            if ($isFx) $netTotal += floatval($dnote->fx_subtotal);
                            else $netTotal += floatval($dnote->subtotal);
                        }
                    }
                }
                $this->income = $netTotal;
                return numberFormat($netTotal);
            })
            ->addColumn('invoice_date', function($project) {
                // invoices from `income` column
                $invoice = $this->invoices->sortBy('invoicedate')->first();
                return $invoice? dateFormat($invoice->invoicedate) : '';
            })
            ->addColumn('sale_items', function($project) {
                $tids = [];
                // invoices from `income` column
                foreach ($this->invoices as $invoice) {
                    $tids[] = '<a href="'. route('biller.invoices.show', $invoice) .'">'. gen4tid('INV-', $invoice->tid) .'</a>';
                    // credit notes
                    foreach ($invoice->creditnotes as $cnote) {
                        $tids[] = '<a href="'. route('biller.creditnotes.show', $cnote) .'">'. gen4tid('CN-', $cnote->tid) .'</a>';
                    }
                    // debit notes
                    foreach ($invoice->debitnotes as $dnote) {
                        $tids[] = '<a href="'. route('biller.creditnotes.show', $dnote) .'">'. gen4tid('DN-', $dnote->tid) .'</a>';
                    }
                }
                return implode('<br>', $tids);
            })
            ->addColumn('expense', function($project) {
                request()->merge(['project_id' => $project->id]);
                $repository = new \App\Repositories\Focus\project\ProjectRepository;
                $controller = new \App\Http\Controllers\Focus\project\ExpensesTableController($repository);
                $expenses = $controller->get_expenses();

                $totalExpense = $expenses->sum(fn($v) => $v->qty * $v->rate);
                $this->expense = round($totalExpense, 4);
                $this->inventoryStock = $expenses->where('exp_category', 'inventory_stock')->sum(fn($v) => $v->qty * $v->rate);
                $this->labourSvc = $expenses->where('exp_category', 'labour_service')->sum(fn($v) => $v->qty * $v->rate);
                $this->dirPurchStock = $expenses->where('exp_category', 'dir_purchase_stock')->sum(fn($v) => $v->qty * $v->rate);
                $this->dirPurchSvc = $expenses->where('exp_category', 'dir_purchase_service')->sum(fn($v) => $v->qty * $v->rate);
                $this->purchOrderStock = $expenses->where('exp_category', 'purchase_order_stock')->sum(fn($v) => $v->qty * $v->rate);                

                return numberFormat($totalExpense);
            })
            ->addColumn('expense_item', function($project) {
                $items = '<b>Inventory Stock: </b>' .numberFormat($this->inventoryStock). ' || ';
                $items .= '<b>Labour Service: </b>' .numberFormat($this->labourSvc). ' || ';
                $items .= '<b>Local Purchase Stock : </b>' .numberFormat($this->dirPurchStock). ' || ';
                $items .= '<b>Local Purchase Service: </b>' .numberFormat($this->dirPurchSvc). ' || ';
                $items .= '<b>Purchase Order Stock: </b>' .numberFormat($this->purchOrderStock);

                return $items;
            })
            ->addColumn('gross_profit', function() {
                $profit = 0;
                if ($this->income > 0) {
                    $profit = $this->income  - $this->expense;
                }
                $this->profit = $profit;
                $this->total_profit += $profit;
                return numberFormat($profit);
            })
            ->addColumn('total_profit', function() {                
                return numberFormat($this->total_profit);
            })
            ->addColumn('ledgers', function($project) use($projectLedgerTotals) {
                $totals = $projectLedgerTotals($project);
                return "
                    <strong>WIP:</strong> " . number_format($totals['wip'], 2) . "<br>
                    <strong>COG Labour:</strong> " . number_format($totals['labour'], 2) . "<br>
                    <strong>COG Material:</strong> " . number_format($totals['material'], 2) . "<br>
                    <strong>COG Transport:</strong> " . number_format($totals['transport'], 2) . "<br>
                    <strong>COG General:</strong> " . number_format($totals['general'], 2) . "
                ";
            })
            ->addColumn('percent_profit', function() {                
                return round(div_num($this->profit, $this->income) * 100);
            })
            ->make(true);
    }

    public function getGpChartData() {

        $data = (new DailyBusinessMetricController())->getProjectGrossProfitData(
            Auth::user()->ins,
            null,
            request('fromDate'),
            request('toDate'),
            request('status'),
            request('customer')
        );

        $payload['income'] = [$data->pluck('income')->sum()];
        $payload['expense'] = [$data->pluck('expense')->sum()];
        $payload['profit'] = [$data->pluck('gross_profit')->sum()];

        return $payload;
    }
}