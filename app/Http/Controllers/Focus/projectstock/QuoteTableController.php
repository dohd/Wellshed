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
namespace App\Http\Controllers\Focus\projectstock;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\quote\QuoteRepository;

class QuoteTableController extends Controller
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
        $core = $this->repository->getForVerifyDataTable();
    
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('checkbox', function ($quote) {
                return '<input type="checkbox" class="select-row" value="'. $quote->id .'">';
            })
            ->addColumn('date', function ($quote) {
                return dateFormat($quote->date);
            })
            ->addColumn('tid', function ($quote) {
                $tid = gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid);
                if ($quote->revision) $tid .= $quote->revision; 

                $url = route('biller.quotes.show', [$quote->id]);
                if ($quote->bank_id) $url = route('biller.quotes.show', [$quote->id, 'page=pi']);

                return '<a href="' . $url . '"><b>'. $tid .'</b></a>';
            })
            ->addColumn('customer', function ($quote) {
                return $quote->branch ? $quote->branch->name : '';
            })
            ->addColumn('item_count', function ($quote) {
                $budget = $quote->budget;
                if ($budget) return $budget->items()->whereHas('product')->count();
            })
            ->addColumn('approved_qty', function ($quote) {
                $budget = $quote->budget;
                if ($budget) {
                    $budget_items = $budget->items()->whereHas('product');
                    return +$budget_items->sum('new_qty');
                }
            })
            ->addColumn('issued_qty', function ($quote) {
                $projectstock = $quote->projectstock;
                if ($projectstock->count()) 

                return +$projectstock->sum('qty_total');                
            })
            ->addColumn('issue_status', function ($quote) {
                $status = 'pending';
                $projectstock = $quote->projectstock;
                if ($projectstock->count()) {
                    $qty = $projectstock->sum('qty_total');
                    $approved_qty = $projectstock->sum('approved_qty_total');
                    if ($approved_qty > $qty)
                        $status = 'partial';
                    else $status = 'complete';
                }

                return $status;
            })
            ->make(true);
    }
}
