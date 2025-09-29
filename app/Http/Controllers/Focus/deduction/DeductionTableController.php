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
namespace App\Http\Controllers\Focus\deduction;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\deduction\DeductionRepository;
//use App\Http\Requests\Focus\deduction\ManagedeductionRequest;

/**
 * Class deductionsTableController.
 */
class DeductionTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var DeductionRepository
     */
    protected $deduction;

    /**
     * contructor to initialize repository object
     * @param deductionRepository $deduction ;
     */
    public function __construct(DeductionRepository $deduction)
    {
        $this->deduction = $deduction;
    }

    /**
     * This method return the data of the model
     * @param ManagedeductionRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        //
        $core = $this->deduction->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($deduction) {
                  return $deduction->name;
                // return '<a href="' . route('biller.ji.index') . '?rel_type=2&rel_id=' . $deduction->id . '">' . $deduction->name . '</a>';
            })
            ->addColumn('brackets', function ($deduction) {
                // return $deduction->users->count('*');
                return $deduction->brackets;
            })
            ->addColumn('rate', function ($deduction) {
                // return $deduction->users->count('*');
                return $deduction->rate;
            })
            ->addColumn('created_at', function ($deduction) {
                return Carbon::parse($deduction->created_at)->toDateString();
            })
            ->addColumn('actions', function ($deduction) {
                // return '<a href="' . route('biller.hrms.index') . '?rel_type=2&rel_id=' . $deduction->id . '" class="btn btn-purple round" data-toggle="tooltip" data-placement="top" title="List"><i class="fa fa-list"></i></a> ' . $deduction->action_buttons;
                return $deduction->action_buttons;
            })
            ->make(true);
    }
}
