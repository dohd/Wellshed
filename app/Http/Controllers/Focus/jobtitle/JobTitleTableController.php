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
namespace App\Http\Controllers\Focus\jobtitle;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\jobtitle\JobTitleRepository;
//use App\Http\Requests\Focus\jobtitle\ManagejobtitleRequest;

/**
 * Class jobtitlesTableController.
 */
class JobTitleTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var jobtitleRepository
     */
    protected $jobtitle;

    /**
     * contructor to initialize repository object
     * @param jobtitleRepository $jobtitle ;
     */
    public function __construct(jobtitleRepository $jobtitle)
    {
        $this->jobtitle = $jobtitle;
    }

    /**
     * This method return the data of the model
     * @param ManagejobtitleRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        //
        $core = $this->jobtitle->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($jobtitle) {
                  return $jobtitle->name;
                // return '<a href="' . route('biller.ji.index') . '?rel_type=2&rel_id=' . $jobtitle->id . '">' . $jobtitle->name . '</a>';
            })
            ->addColumn('department', function ($jobtitle) {
                // return $jobtitle->users->count('*');
                return $jobtitle->department;
            })
            ->addColumn('created_at', function ($jobtitle) {
                return Carbon::parse($jobtitle->created_at)->toDateString();
            })
            ->addColumn('actions', function ($jobtitle) {
                // return '<a href="' . route('biller.hrms.index') . '?rel_type=2&rel_id=' . $jobtitle->id . '" class="btn btn-purple round" data-toggle="tooltip" data-placement="top" title="List"><i class="fa fa-list"></i></a> ' . $jobtitle->action_buttons;
                return $jobtitle->action_buttons;
            })
            ->make(true);
    }
}
