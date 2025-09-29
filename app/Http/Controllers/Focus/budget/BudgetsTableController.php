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
namespace App\Http\Controllers\Focus\budget;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\budget\BudgetRepository;
use Illuminate\Support\Facades\DB;
use Request;
use Yajra\DataTables\Facades\DataTables;


class BudgetsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var BudgetRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param BudgetRepository $repository ;
     */
    public function __construct(BudgetRepository $repository)
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
        $prefixes = prefixesArray(['quote', 'proforma_invoice'], auth()->user()->ins);

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($budget) use($prefixes) {
                if ($budget->quote) {
                    $quote = $budget->quote;
                    return gen4tid($quote->bank_id? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid);
                }
            })
            ->addColumn('customer', function ($budget) {
                if ($budget->quote) {
                    $quote = $budget->quote;
                    $customer = $quote->customer? $quote->customer->company : '';
                    if ($quote->branch) $customer .= " - {$quote->branch->name}";

                    return $customer;
                }
            })
            ->addColumn('note', function ($budget) {
                if ($budget->quote) return $budget->quote->notes;
            })
            ->addColumn('quote_total', function ($budget) {
                if ($budget->quote)
                return numberFormat($budget->quote->total);
            })
            ->addColumn('budget_total', function ($budget) {
                $total = $budget->items()->sum(DB::raw('round(new_qty*price)'));
                return numberFormat($total);
            })
            ->addColumn('actions', function ($budget) {
                return '<a href="' . route('biller.budgets.show', $budget) . '" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="View"><i  class="fa fa-eye"></i></a>'
                .$budget->action_buttons;
            })
            ->make(true);
    }
}
