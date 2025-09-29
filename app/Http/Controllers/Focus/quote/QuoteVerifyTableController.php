<?php

namespace App\Http\Controllers\Focus\quote;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\quote\QuoteRepository;

/**
 * Class QuotesTableController.
 */
class QuoteVerifyTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var QuoteRepository
     */
    protected $quote;

    /**
     * contructor to initialize repository object
     * @param QuoteRepository $quote ;
     */
    public function __construct(QuoteRepository $quote)
    {
        $this->quote = $quote;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->quote->getForVerifyDataTable();
        $prefixes = prefixesArray(['quote', 'proforma_invoice', 'project'], auth()->user()->ins);

        $expenseService = new QuotesController($this->quote);
        $expenseData = $expenseService->calculateExpensesForQuotes($core->get());

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($quote) use($prefixes) {
                $tid = gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid);
                return '<a class="font-weight-bold" href="'. route('biller.quotes.show',$quote) .'">'. $tid . $quote->revision .'</a>';
            })
            ->filterColumn('tid', function ($query, $keyword) use ($prefixes) {
                $query->where(function ($q) use ($keyword, $prefixes) {
                    $q->where('tid', 'like', "%{$keyword}%")
                      ->orWhereRaw('CONCAT(?, tid, revision) like ?', ["{$prefixes[0]}-", "%{$keyword}%"])
                      ->orWhereRaw('CONCAT(?, tid, revision) like ?', ["{$prefixes[1]}-", "%{$keyword}%"]);
                });
            })
            ->addColumn('customer', function ($quote) {
                $customer = $quote->lead? $quote->lead->client_name : '';
                if ($quote->customer) {
                    $customer = "{$quote->customer->company}";
                    if ($quote->branch) $customer .= " - {$quote->branch->name}";
                }
                
                return $customer;
            })
            ->filterColumn('customer', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('company', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('lead', function ($q) use ($keyword) {
                        $q->where('client_name', 'like', "%{$keyword}%");
                    });
                });
            })
            ->addColumn('total', function ($quote) {
                if ($quote->currency) return amountFormat($quote->total, $quote->currency->id);
                return numberFormat($quote->total);
            })
            ->orderColumn('total', function ($query, $order) {
                $query->orderBy('total', $order);
            })
            ->filterColumn('total', function ($query, $keyword) {
                $query->where('total', 'like', "%{$keyword}%");
            })
            ->addColumn('verified_total', function ($quote) {
                if ($quote->currency) return amountFormat($quote->verified_total, $quote->currency->id);
                return numberFormat($quote->verified_total);
            })
            ->addColumn('lpo_number', function($quote) {
                if ($quote->lpo) return 'lpo - ' . $quote->lpo->lpo_no;
            })
            ->filterColumn('lpo_number', function ($query, $keyword) {
                $query->whereHas('lpo', function ($q) use ($keyword) {
                    $q->where('lpo_no', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('approved_date', function($quote) {
                return $quote->approved_date? dateFormat($quote->approved_date) : '';
            })
            ->orderColumn('approved_date', 'approved_date $1')
            ->addColumn('date', function($quote) {
                return $quote->date? dateFormat($quote->date) : '';
            })
            ->orderColumn('date', 'date $1')
            ->addColumn('project_tid', function($quote) use($prefixes) {
                $project = $quote->project;
                if ($project) {                 
                    return "<a href=". route('biller.projects.show', $project) .">". gen4tid("{$prefixes[2]}-", $project->tid) ."</a>";
                }
            })
            ->filterColumn('project_tid', function ($query, $keyword) use ($prefixes) {
                $query->whereHas('project', function ($q) use ($keyword, $prefixes) {
                    $q->where('tid', 'like', "%{$keyword}%")
                      ->orWhereRaw('CONCAT(?, tid) like ?', ["{$prefixes[2]}-", "%{$keyword}%"]);
                });
            })
             ->addColumn('project_closure_date', function($quote) {
                return $quote->project_closure_date? dateFormat($quote->project_closure_date) : '';
            })
             ->addColumn('expenses', function($quote) use ($expenseData) {
                $data = $expenseData[$quote->id] ?? ['profit' => 0, 'percent_profit' => 0];

                $profit = $data['profit'];
                $percent_profit = $data['percent_profit'];

                $profitClass = $profit < 0 ? 'text-danger' : 'text-success';
                $percentClass = $percent_profit < 0 ? 'text-danger' : 'text-success';

                return '<b>Current Profit Amount: </b> <span class="' . $profitClass . '">' . numberFormat($profit) . '</span> | '
                    . '<b>Percentage Profit: </b> <span class="' . $percentClass . '">' . numberFormat($percent_profit) . '%</span>';
            })
            ->orderColumn('project_closure_date', 'project_closure_date $1')
            ->addColumn('actions', function ($quote) {
                $valid_token = token_validator('', 'q'.$quote->id .$quote->tid, true);
                if ($quote->verified == 'No') {
                    return '<a href="'. route('biller.quotes.verify', $quote) .'" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="Verify">
                        <i class="fa fa-check"></i></a>';
                }
                    
                return '<a href="'.route('biller.print_verified_quote', [$quote->id, 4, $valid_token, 1, 'verified=Yes']).'" class="btn btn-purple round" target="_blank" data-toggle="tooltip" data-placement="top" title="Print">
                    <i class="fa fa-print"></i></a> '
                    .'<a href="'. route('biller.quotes.verify', $quote) .'" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="Verify">
                    <i class="fa fa-check"></i></a>';
            })
            ->make(true);
    }
}
