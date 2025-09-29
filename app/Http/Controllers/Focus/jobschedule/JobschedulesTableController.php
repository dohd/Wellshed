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
namespace App\Http\Controllers\Focus\jobschedule;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\jobschedule\JobscheduleRepository;
use App\Http\Requests\Focus\jobschedule\ManageJobscheduleRequest;

/**
 * Class BranchTableController.
 */
class JobschedulesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $jobschedule;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(JobscheduleRepository $jobschedule)
    {

        $this->jobschedule = $jobschedule;
    }

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageJobscheduleRequest $request)
    {
    

        $core = $this->jobschedule->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
              
            ->addColumn('name', function ($jobschedule) {

                 return '<a class="font-weight-bold" href="' . route('biller.jobschedules.show', [$jobschedule->id]) . '">' . $jobschedule->projects->name . '</a>';



                //return '<a class="font-weight-bold" href="' . route('biller.regions.index') . '?rel_type=' . $jobschedule->id . '&rel_id=' . $jobschedule->id . '">' . $jobschedule->projects->name . '</a>';
            })
        
             ->addColumn('customer', function ($jobschedule) {
              

                return $jobschedule->customer->company;
                })
                ->addColumn('start_date', function ($jobschedule) {
              

                return dateFormat($jobschedule->expected_end_date);
                })
           ->addColumn('status', function ($jobschedule) {
                $task_back = task_status($jobschedule->status);
                return $task_back['name']; 
            })

              ->addColumn('created_at', function ($jobschedule) {
                return dateFormat($jobschedule->created_at);
            })
            ->addColumn('actions', function ($jobschedule) {
                return $jobschedule->action_buttons;
               // return '<a class="btn btn-purple round" href="' . route('biller.branches.index') . '?rel_type=' . $branch->id . '&rel_id=' . $branch->id . '" title="List"><i class="fa fa-list"></i></a>' . $branch->action_buttons;
            })
            ->make(true);
    }
}
