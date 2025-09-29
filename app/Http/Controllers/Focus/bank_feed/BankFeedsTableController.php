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

 namespace App\Http\Controllers\Focus\bank_feed;

use App\Http\Controllers\Controller;
use App\Models\bank\BankFeed;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class BranchTableController.
 */
class BankFeedsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct()
    {
        // $this->branch = $repository;
    }

    /**
     * This method return the data of the model
     *
     * @return mixed
     */
    public function __invoke()
    {
        $core = BankFeed::latest()->get();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('row_check', function ($feed) {
                return '<input type="checkbox" class="checkRow" data-id="'. $feed->id .'">';        
            })
            ->editColumn('trans_time', function ($feed) {
                $chunks = str_split($feed->trans_time, 2);
                $date = "{$chunks[0]}-{$chunks[1]}-{$chunks[2]}"; //YY-mm-dd-HH-mm
                return date('d-m-Y', strtotime(date($date)));
            })
            ->editColumn('narrative', function ($feed) {
                return $feed->narrative;
            })
            ->editColumn('customer_name', function ($feed) {
                return $feed->customer_name;
            })
            ->editColumn('trans_amount', function ($feed) {
                return numberFormat($feed->trans_amount);
            })
            ->addColumn('spent', function ($feed) {
                $amount = '';
                $transType = $this->checkTransType($feed->trans_type);
                if ($transType == 'in/out' && $feed->trans_amount < 0)
                    $amount = numberFormat($feed->trans_amount);
                if ($transType == 'out') $amount = numberFormat($feed->trans_amount);
                return $amount;
            })
            ->addColumn('received', function ($feed) {
                $amount = '';
                $transType = $this->checkTransType($feed->trans_type);
                if ($transType == 'in/out' && $feed->trans_amount > 0)
                    $amount = numberFormat($feed->trans_amount);
                if ($transType == 'in') $amount = numberFormat($feed->trans_amount);
                return $amount;
            })
            ->addColumn('actions', function ($feed) {
                return '<div style="margin-bottom:4px"><i class="fa fa-location-arrow" aria-hidden="true"></i> <a href="#">Categorize</a></div>
                    <div style="margin-bottom:4px"><i class="fa fa-search" aria-hidden="true"></i> <a href="#">Find Match</a></div>
                    <div style="margin-bottom:2px"><i class="fa fa-exchange" aria-hidden="true"></i> <a href="#">Record As Transfer</a></div>';
            })
            ->make(true);
    }

    // Check Transaction Type
    public function checkTransType($transType)
    {
        switch ($transType) {
            case 875: return 'out'; // MPESA Transfer Debit
            case 876: return 'in'; // MPESA Transfer Credit
            case 900: return 'out'; // IB Outgoing Mobile Payments
            case 934: return 'in'; // Inward Clearing EFT
            case 935: return 'in'; // Inward Clearing EFT
            case 433: return ''; // SL Loan Rollover
            case 264: return 'in/out'; // Internal Transfer
            case 265: return 'in/out'; // Internal Transfer
            default: return '';
        }
    }
}
