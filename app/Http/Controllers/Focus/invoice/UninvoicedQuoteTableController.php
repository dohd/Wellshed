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
namespace App\Http\Controllers\Focus\invoice;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\quote\QuoteRepository;

/**
 * Class QuotesTableController.
 */
class UninvoicedQuoteTableController extends Controller
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
        $core = $this->repository->getForVerifyNotInvoicedDataTable();
        $prefixes = prefixesArray(['quote', 'proforma_invoice', 'project'], auth()->user()->ins);
        
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('mass_select', function ($quote) {
                return  '<input type="checkbox"  class="row-select" value="'. $quote->id .'">';
            })
            ->addColumn('title', function($quote) {
                return $quote->notes;
            })
            ->addColumn('tid', function ($quote) use($prefixes) {
                $tid = gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid);
                return '<a class="font-weight-bold" href="'. route('biller.quotes.show', $quote) .'">' . $tid . $quote->revision .'</a>';
            })
            ->addColumn('customer', function ($quote) {
                $customer = $quote->lead? $quote->lead->client_name : '';
                if ($quote->client) {
                    $customer = "{$quote->client->company}";
                    if ($quote->branch) $customer .= " - {$quote->branch->name}";
                }
                return $customer;
            })
            ->addColumn('created_at', function ($quote) {
                return dateFormat($quote->invoicedate);
            })
            ->addColumn('total', function ($quote) {
                return numberFormat($quote->total);
            })
            ->addColumn('verified_total', function ($quote) {
                return numberFormat($quote->verified_total);
            })
            ->addColumn('diff_total', function ($quote) {
                return numberFormat($quote->total - $quote->verified_total);
            })
            ->addColumn('project_tid', function($quote) use($prefixes) {
                if ($quote->project) 
                return gen4tid("{$prefixes[2]}-", $quote->project->tid);
            })
            ->addColumn('lpo_number', function($quote) {
                if (!$quote->lpo)  return 'Null:';
                $amount = numberFormat($quote->lpo->amount);
                $currency = $quote->currency? $quote->currency->code : '';
                return $quote->lpo->lpo_no . "<br> {$currency}: {$amount}";
            })
            ->make(true);
    }
}
