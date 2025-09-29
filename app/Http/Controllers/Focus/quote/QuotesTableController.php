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
namespace App\Http\Controllers\Focus\quote;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\quote\QuoteRepository;

/**
 * Class QuotesTableController.
 */
class QuotesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var QuoteRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param QuoteRepository $repository ;
     */
    public function __construct(QuoteRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $query = $this->repository->getForDataTable();

        $query_1 = clone $query->latest()->take(1000);
        $sum_total = $query_1->sum('total');

        $core = clone $query;
        $query_2 = clone $query;

        $total_created = $core->count();
        $total_approved = $core->whereNotNull('approved_by')->where('status', 'approved')->count();
        $total_not_approved = $query_2->whereNull('approved_by')->count();


        $totalPercentage = $total_created > 0 ? round(($total_created / $total_created) * 100, 2) : 0;
        $approvalPercentage = $total_created > 0 ? round(($total_approved / $total_created) * 100, 2) : 0;
        $notApprovalPercentage = $total_created > 0 ? round(($total_not_approved / $total_created) * 100, 2) : 0;
        $aggregate = compact('total_not_approved', 'total_approved','total_created', 'totalPercentage','approvalPercentage','notApprovalPercentage');
        // dd($aggregate);

        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['quote', 'proforma_invoice', 'lead', 'invoice'], $ins);

        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('tid', function ($quote) use($prefixes) {               
                $link = route('biller.quotes.show', [$quote->id]);
                if ($quote->bank_id) $link = route('biller.quotes.show', [$quote->id, 'page=pi']);
                return '<a class="font-weight-bold" href="' . $link . '">' . gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid) . '</a>';
            })
            ->filterColumn('tid', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (strtolower($arr[0]) == strtolower($prefixes[1]) && isset($arr[1])) {
                    $query->where('tid', floatval($arr[1]));
                } elseif (floatval($tid)) {
                    $query->where('tid', floatval($tid));
                }
            })
            ->addColumn('customer', function ($quote) {
                $customer = '';
                if ($quote->customer) {
                    $customer .= $quote->customer->company;
                    if ($quote->branch) $customer .= " - {$quote->branch->name}";
                } elseif ($quote->lead) {
                    $customer .= $quote->lead->client_name;
                }

                return $customer;
            })
            ->addColumn('source', function ($quote) {
                $lead = $quote->lead;

                if (!empty($lead)){
                    $leadSource = $lead->LeadSource;

                    if (!empty($leadSource)) return $leadSource->name;
                    else return '<b><i> N/A </i></b>';
                }
                else return '<b><i> N/A </i></b>';
            })
            ->addColumn('date', function ($quote) {
                return dateFormat($quote->date);
            })
            ->orderColumn('date', '-date $1')
            ->addColumn('total', function ($quote) {
                if ($quote->taxable > 0) $this->quote_taxable_amount = $quote->taxable;
                else $this->quote_taxable_amount = $quote->subtotal;
                
                return numberFormat($quote->total);
            })   
//            ->addColumn('exp_total', function ($quote) {
//                $project = $quote->project;
//                if ($project) {
//                    $no_quotes = $project->quotes()->count();
//
//                    $issued_stock_amount = $quote->projectstock? $quote->projectstock()->sum('total') : 0;
//                    $dir_purchase_amount = $project->purchase_items->sum('amount');
//                    $grn_amount = $project->grn_items()->sum(DB::raw('rate*qty'));
//                    $labour_amount = $project->labour_allocations()->sum(DB::raw('hrs * 500'));
//                    $expense_amount = (($dir_purchase_amount + $grn_amount + $labour_amount) / $no_quotes) + $issued_stock_amount;
//
//                    $this->quote_expense_total = $expense_amount;
//                    if ($expense_amount > 0)
//                    return '<a href="'. route('biller.projects.show', ['project' => $project, 'tab' => 'expense']) .'" key="'. $project->id .'">'
//                        . numberFormat($expense_amount) .'</a>' ;
//                }
//            })
//            ->addColumn('exp_diff', function ($quote) {
//                if ($quote->project && $this->quote_expense_total > 0)
//                return numberFormat($this->quote_taxable_amount - $this->quote_expense_total);
//            })
            ->addColumn('approved_date', function ($quote) {
                return $quote->approved_date? dateFormat($quote->approved_date) : '';
            })
            ->orderColumn('approved_date', '-approved_date $1')
            ->editColumn('lead_tid', function($quote) use($prefixes) {
                $link = '';
                if ($quote->lead) {
                    $link = '<a href="'. route('biller.leads.show', $quote->lead) .'">'.gen4tid("{$prefixes[2]}-", $quote->lead->reference).'</a>';
                    if (auth()->user()->customer_id) $link = gen4tid("{$prefixes[2]}-", $quote->lead->reference);
                }
                return $link;
            })
            ->filterColumn('lead_tid', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (strtolower($arr[0]) == strtolower($prefixes[2]) && isset($arr[1])) {
                    $query->whereHas('lead', fn($q) => $q->where('reference', floatval($arr[1])));
                } elseif (floatval($tid)) {
                    $query->whereHas('lead', fn($q) => $q->where('reference', floatval($tid)));
                }
            })
            ->addColumn('invoice_tid', function ($quote) use($prefixes) {
                $inv_product = $quote->invoice_product;
                if (@$inv_product->invoice) return gen4tid("{$prefixes[3]}-", $inv_product->invoice->tid);
                elseif(@$quote->invoice_quote) return gen4tid("{$prefixes[3]}-", $quote->invoice_quote->tid);
            })
            ->filterColumn('invoice_tid', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (strtolower($arr[0]) == strtolower($prefixes[3]) && isset($arr[1])) {
                    $query->whereHas('invoice', fn($q) => $q->where('tid', floatval($arr[1])));
                } elseif (floatval($tid)) {
                    $query->whereHas('invoice', fn($q) => $q->where('tid', floatval($tid)));
                }
            })
            ->addColumn('sum_total', function($quote) use($sum_total) {
                return numberFormat($sum_total);
            })
            ->addColumn('aggregate', function($quote) use($aggregate) {
                return $aggregate;
            })
            ->addColumn('budget_status', function ($quote) {
                return $quote->budget? '<span class="badge badge-success">budgeted</span>' : 
                    '<span class="badge badge-secondary">pending</span>';
            })
            ->addColumn('actions', function ($quote) {
                $action_buttons = $quote->action_buttons;
                if (request('page') == 'pi') {
                    $name = 'biller.quotes.show';
                    $action_buttons = str_replace(route($name, $quote), route($name, [$quote, 'page=pi']), $action_buttons);
                }
                $valid_token = token_validator('', 'q' . $quote->id . $quote->tid, true);
                $copy_text = $quote->bank_id ? 'PI Copy' : 'Quote Copy';
                $task = $quote->bank_id ? 'page=pi&task=pi_to_pi' : 'task=quote_to_quote';

                $copyLink = '<a href="'.route('biller.quotes.edit', [$quote, $task]).'" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="'. $copy_text .'"><i class="fa fa-clone" aria-hidden="true"></i></a> ';
                if (auth()->user()->customer_id) $copyLink = '';

                return '<a href="'.route('biller.print_quote', [$quote->id, 4, $valid_token, 1]).'" class="btn btn-purple round" target="_blank" data-toggle="tooltip" data-placement="top" title="Print"><i class="fa fa-print"></i></a> '
                    . $copyLink . $action_buttons;
                    
            })
            ->make(true);
    }
}
