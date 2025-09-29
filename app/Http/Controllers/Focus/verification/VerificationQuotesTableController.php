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
namespace App\Http\Controllers\Focus\verification;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\verification\VerificationRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class VerificationQuotesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var VerificationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param VerificationRepository $repository ;
     */
    public function __construct(VerificationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForVerificationQuoteDataTable();
        $prefixes = prefixesArray(['quote', 'proforma_invoice', 'project'], auth()->user()->ins);

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('checkbox', function ($quote) {
                return '<input type="checkbox" class="select-row" value="'. $quote->id .'">';
            })
            ->addColumn('tid', function ($quote) use($prefixes) {
                $tid = gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid);
                return '<a class="font-weight-bold" href="'. route('biller.quotes.show',$quote) .'">'. $tid . $quote->revision .'</a>';
            })
            ->addColumn('customer', function ($quote) {
                $customer = $quote->lead? $quote->lead->client_name : '';
                if ($quote->customer) {
                    $customer = "{$quote->customer->company}";
                    if ($quote->branch) $customer .= " - {$quote->branch->name}";
                }
                
                return $customer;
            })
            ->addColumn('total', function ($quote) {
                if ($quote->currency) 
                    return amountFormat($quote->total, $quote->currency->id);
                return numberFormat($quote->total);
            })
            ->addColumn('verified_total', function ($quote) {
                if ($quote->currency) 
                    return amountFormat($quote->verified_total, $quote->currency->id);
                return numberFormat($quote->verified_total);
            })
            ->addColumn('balance', function ($quote) {
                $balance = $quote->total - $quote->verified_total;
                if (round($quote->verified_total) > round($quote->total)) $balance = 0;
                if ($quote->currency) 
                    return amountFormat($balance, $quote->currency->id);
                return numberFormat($quote->verified_total);
            })
            ->addColumn('lpo_number', function($quote) {
                if ($quote->lpo) return 'lpo - ' . $quote->lpo->lpo_no;
            })
            ->addColumn('project_tid', function($quote) use($prefixes) {
                if ($quote->project) 
                return gen4tid("{$prefixes[2]}-", $quote->project->tid);
            })
            ->addColumn('date', function($quote) {
                return dateFormat($quote->date);
            })
            ->make(true);
    }
}
