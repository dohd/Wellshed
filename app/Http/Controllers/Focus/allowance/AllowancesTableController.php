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
namespace App\Http\Controllers\Focus\allowance;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\allowance\AllowanceRepository;
use App\Http\Requests\Focus\allowance\ManageAllowanceRequest;

/**
 * Class DepartmentsTableController.
 */
class AllowancesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var AllowanceRepository
     */
    protected $allowance;

    /**
     * contructor to initialize repository object
     * @param AllowanceRepository $allowance ;
     */
    public function __construct(AllowanceRepository $allowance)
    {
        $this->allowance = $allowance;
    }

    /**
     * This method return the data of the model
     * @param AllowancementRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageAllowanceRequest $request)
    {
        //
        $core = $this->allowance->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($allowance) {
                //  return $department->name;
                return '<a href="' . route('biller.allowances.index') . '?rel_type=2&rel_id=' . $allowance->id . '">' . $allowance->name . '</a>';
            })
            ->addColumn('created_at', function ($allowance) {
                return dateFormat($allowance->created_at);
            })
            ->addColumn('actions', function ($allowance) {
                return '<a href="' . route('biller.allowances.index') . '?rel_type=2&rel_id=' . $allowance->id . '" class="btn btn-purple round" data-toggle="tooltip" data-placement="top" title="List"><i class="fa fa-list"></i></a> ' . $allowance->action_buttons;
            })
            ->make(true);
    }
}
