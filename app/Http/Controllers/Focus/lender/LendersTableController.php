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
namespace App\Http\Controllers\Focus\lender;

use App\Http\Requests\Focus\general\ManageCompanyRequest;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\lender\LenderRepository;
use App\Http\Requests\Focus\lender\ManageLenderRequest;

/**
 * Class LendersTableController.
 */
class LendersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var LenderRepository
     */
    protected $lender;

    /**
     * contructor to initialize repository object
     * @param LenderRepository $bank ;
     */
    public function __construct(LenderRepository $lender)
    {
        $this->lender = $lender;
    }

    /**
     * This method return the data of the model
     * @param ManageLenderRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageCompanyRequest $request)
    {
        //
        $core = $this->lender->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('created_at', function ($lender) {
                return dateFormat($lender->created_at);
            })
            ->addColumn('actions', function ($lender) {
                return $lender->action_buttons;
            })
            ->make(true);
    }
}
