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
namespace App\Http\Controllers\Focus\commission;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\commission\CommissionRepository;

/**
 * Class commissionsTableController.
 */
class CommissionsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var commissionRepository
     */
    protected $commission;

    /**
     * contructor to initialize repository object
     * @param commissionRepository $commission ;
     */
    public function __construct(CommissionRepository $commission)
    {
        $this->commission = $commission;
    }

    /**
     * This method return the data of the model
     * @param ManagecommissionRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->commission->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('title', function ($commission) {
                 return $commission->title;
            })
            ->addColumn('tid', function ($commission) {
                return gen4tid('CM-',$commission->tid);
            })
            ->addColumn('created_at', function ($commission) {
                return Carbon::parse($commission->created_at)->toDateString();
            })
            ->addColumn('actions', function ($commission) {
                return $commission->action_buttons;
            })
            ->make(true);
    }
}
