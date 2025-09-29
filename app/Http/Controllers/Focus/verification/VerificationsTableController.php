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


class VerificationsTableController extends Controller
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
        $core = $this->repository->getForDataTable();
        $prefixes = prefixesArray(['quote', 'proforma_invoice', 'project'], auth()->user()->ins);

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('checkbox', function ($verification) {
                return '<input type="checkbox" class="select-row" value="'. $verification->id .'">';
            })
            ->addColumn('tid', function ($verification) {
                return $verification->tid;
            })
            ->addColumn('quote_tid', function ($verification) use($prefixes) {
                $quote = $verification->quote;
                if ($quote) {
                    $tid = gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid);
                    return '<a class="font-weight-bold" href="'. route('biller.quotes.show',$quote) .'">'. $tid . $quote->revision .'</a>';
                }
            })
            ->addColumn('customer', function ($verification)  {
                $customer = '';
                if ($verification->customer) {
                    $customer = $verification->customer->company;
                    if ($verification->branch) $customer .= " - {$verification->branch->name}";
                }
                
                return $customer;
            })
            ->addColumn('total', function ($verification) {
                return numberFormat($verification->total);
            })
            ->addColumn('lpo_no', function($verification) {
                $quote = $verification->quote;
                if ($quote && $quote->lpo) return 'lpo - ' . $quote->lpo->lpo_no;
            })
            ->addColumn('project_no', function($verification) use($prefixes) {
                $quote = $verification->quote;
                if ($quote && $quote->project) return gen4tid("{$prefixes[2]}-", $quote->project->tid);
            })
            ->addColumn('actions', function ($verification) {
                return $verification->action_buttons;
            })
            ->make(true);
    }
}
