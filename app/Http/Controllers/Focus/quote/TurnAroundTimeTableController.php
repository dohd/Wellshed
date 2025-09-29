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
use App\Models\invoice\PaidInvoice;

/**
 * Class QuotesTableController.
 */
class TurnAroundTimeTableController extends Controller
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
        $core = $this->repository->getForTurnAroundTime();

        $sum_total = numberFormat($core->sum('total'));

        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['quote', 'proforma_invoice', 'lead', 'invoice', 'project','djc_report','rjc_report','jobcard','payment'], $ins);

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($quote) use($prefixes) {               
                $link = route('biller.quotes.show', [$quote->id]);
                if ($quote->bank_id) $link = route('biller.quotes.show', [$quote->id, 'page=pi']);

                if (auth()->user()->customer_id) return gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid);
                return '<a class="font-weight-bold" href="' . $link . '">' . gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid) . '</a>';
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
            ->addColumn('date', function ($quote) {
                return dateFormat($quote->date);
            })
            ->addColumn('total', function ($quote) {
                if ($quote->currency) return amountFormat($quote->total, $quote->currency->id);
                return numberFormat($quote->total);
            })   
            ->addColumn('approved_date', function ($quote) {
                return $quote->approved_date? dateFormat($quote->approved_date) : '';
            })
            ->addColumn('lead_tid', function($quote) use($prefixes) {
                $link = '';
                if ($quote->lead) {
                    $link = '<a href="'. route('biller.leads.show', $quote->lead) .'">'.gen4tid("{$prefixes[2]}-", $quote->lead->reference).'</a>';
                }
                if (auth()->user()->customer_id && $quote->lead) {
                    $link = gen4tid("{$prefixes[2]}-", $quote->lead->reference);
                }
                return $link;
            })
            ->addColumn('lead_date', function($quote){
                $date = '';
                if($quote->lead){
                    $date = dateFormat($quote->lead->date_of_request);
                }
                return $date;
            })
            ->addColumn('djcs_tid', function($quote) use($prefixes) {
                
                $lead = $quote->lead;
                if ($lead) {
                    $link = [];
                    foreach ($lead->djcs as $djcs) {
                        $link = $djcs->tid;
                    }
                    //$link[] = $lead->djcs->cou ? $lead->djcs->tid : '';
                }
                return $link;
            })
            ->addColumn('djcs_date', function($quote){
                $date = '';
                if($quote->lead){
                    if(count($quote->lead->djcs) > 0){
                        $djc = $quote->lead->djcs()->first();
                        $date = dateFormat($djc->jobcard_date);
                        // dd($quote->lead->djcs);
                    }
                //    $date = dateFormat($quote->lead->djcs->jobcard_date);
                }
                return $date;
            })
            ->addColumn('project_no', function($quote) use($prefixes){
                $project = ''; 
                if($quote->project){
                    $project = gen4tid("{$prefixes[4]}-", $quote->project->tid);
                }
                return $project;
            })
            ->addColumn('project_date', function($quote){
                $date = '';
                if($quote->project){
                    $date = dateFormat($quote->project->start_date);
                }
                return $date;
            })
            ->addColumn('project_closure_date', function($quote) {
                return $quote->project_closure_date? dateFormat($quote->project_closure_date) : '';
            })
            ->addColumn('approval_date', function($quote){
                return $quote->approved_date? dateFormat($quote->approved_date) : '';
            })
            ->addColumn('invoice_tid', function ($quote) use($prefixes) {
                $inv_product = $quote->invoice_product;
                if (@$inv_product) return gen4tid("{$prefixes[3]}-", $inv_product->invoice->tid);
            })
            ->addColumn('invoice_date', function ($quote) use($prefixes) {
                $inv_product = $quote->invoice_product;
                if ($inv_product) return dateFormat($inv_product->invoice->invoicedate);
            })
            ->addColumn('payment_tid', function ($quote) use($prefixes) {
                $inv_product = $quote->invoice_product;
                $tid = '';
                if ($inv_product) {
                    $ti = $inv_product->invoice ? $inv_product->invoice->payments : '';
                    if ($inv_product->invoice->payments) {
                        //$tid = $inv_product->invoice->payments->count() ? $inv_product->invoice->payments->first()->paid : '';
                        $tid = $inv_product->invoice->payments->count() > 0 ? @PaidInvoice::find($inv_product->invoice->payments->first()->paidinvoice_id)->tid : '';
                        $tid = gen4tid("PMT-", $tid);
                        
                    }
                }
                return $tid;
            })
            ->addColumn('payment_date', function ($quote) use($prefixes) {
                $inv_product = $quote->invoice_product;
                $date = '';
                if ($inv_product) {
                    $ti = $inv_product->invoice ? $inv_product->invoice->payments : '';
                    if ($inv_product->invoice->payments) {
                        //$tid = $inv_product->invoice->payments->count() ? $inv_product->invoice->payments->first()->paid : '';
                        $date = $inv_product->invoice->payments->count() > 0 ? @PaidInvoice::find($inv_product->invoice->payments->first()->paidinvoice_id)->date : '';
                        $date = dateFormat($date);
                        // dd($tid);
                        
                    }
                }
                return $date;
            })
            ->addColumn('rjcs', function($quote) use($prefixes){
                $rjcs = '';
                if($quote->project){
                    $rjcs = $quote->project->rjc ? gen4tid("{$prefixes[6]}-", $quote->project->rjc->tid) : '';
                }
                return $rjcs;
            })
            ->addColumn('rjcs_date', function($quote) use($prefixes){
                $rjcs = '';
                if($quote->project){
                    $rjcs = $quote->project->rjc ? dateFormat($quote->project->rjc->report_date) : '';
                }
                return $rjcs;
            })
            ->addColumn('sum_total', function ($quote) use($sum_total) {
                return $sum_total;
            })
            ->addColumn('actions', function ($quote) {
                $action_buttons = $quote->action_buttons;
                if (request('page') == 'pi') {
                    $name = 'biller.quotes.show';
                    $action_buttons = str_replace(route($name, $quote), route($name, [$quote, 'page=pi']), $action_buttons);
                }
                $valid_token = token_validator('', 'q'.$quote->id .$quote->tid, true);
                $copy_text = $quote->bank_id ? 'PI Copy' : 'Quote Copy';
                $task = $quote->bank_id ? 'page=pi&task=pi_to_pi' : 'task=quote_to_quote';

                return '<a href="'.route('biller.print_quote', [$quote->id, 4, $valid_token, 1]).'" class="btn btn-purple round" target="_blank" data-toggle="tooltip" data-placement="top" title="Print"><i class="fa fa-print"></i></a> '
                    .'<a href="'.route('biller.quotes.edit', [$quote, $task]).'" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="'. $copy_text .'"><i class="fa fa-clone" aria-hidden="true"></i></a> '
                    .$action_buttons;
            })
            ->make(true);
    }
}
