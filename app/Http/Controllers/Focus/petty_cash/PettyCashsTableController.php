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
namespace App\Http\Controllers\Focus\petty_cash;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Repositories\Focus\petty_cash\PettyCashRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class PettyCashsTableController.
 */
class PettyCashsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var PettyCashRepository
     */
    protected $petty_cash;

    /**
     * contructor to initialize repository object
     * @param PettyCashRepository $petty_cash ;
     */
    public function __construct(PettyCashRepository $petty_cash)
    {
        $this->petty_cash = $petty_cash;
    }

    /**
     * This method return the data of the model
     * @param Managepetty_cashRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->petty_cash->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('date', function ($petty_cash) {
                 return dateFormat($petty_cash->date);
            })
            ->addColumn('expected_date', function ($petty_cash) {
                 return dateFormat($petty_cash->expected_date);
            })
            ->addColumn('pr_no', function ($petty_cash) {
                return $petty_cash->pr ? gen4tid('PR-',$petty_cash->pr->tid) : '';
            })
            ->addColumn('created_at', function ($petty_cash) {
                return Carbon::parse($petty_cash->created_at)->toDateString();
            })
            ->addColumn('actions', function ($petty_cash) {
                return $petty_cash->action_buttons;
            })
            ->make(true);
    }
}
