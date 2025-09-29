<?php

namespace App\Http\Controllers\Focus\leave;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\leave\LeaveRepository;
use DateTime;
use DB;
use Request;
use Yajra\DataTables\Facades\DataTables;


class LeaveTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var LeaveRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param LeaveRepository $repository ;
     */
    public function __construct(LeaveRepository $repository)
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

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('employee', function ($leave) {
                $employee = $leave->employee;
                if ($employee) 
                return $employee->first_name . ' ' . $employee->last_name;
            })
            ->addColumn('leave_category', function ($leave) {
                $category = $leave->leave_category;
                if ($category) 
                return $category->title;
            })
            ->addColumn('submission', function ($leave) {
                return (new DateTime($leave->created_at))->format('jS M Y');
            })
            ->addColumn('start_date', function ($leave) {
                return dateFormat($leave->start_date);
            })
            ->addColumn('end_date', function ($leave) {
                return dateFormat($leave->end_date);
            })
            ->addColumn('return_date', function ($leave) {
                $endDate = dateFormat($leave->end_date);
                $holidays = DB::table('leave_holidays')->pluck('date')->toArray();
                $return_date = calculateReturnToWorkDate($endDate, $holidays);
                return dateFormat($return_date);
            })
            ->addColumn('status', function ($leave) {
                $no_of_approvers = count(explode(',',$leave->approver_ids));
                $approvals = $leave->approvers; // Collection of approver decisions

                if ($approvals->contains('status', 'rejected')) {
                    $overallStatus = 'Rejected';
                } elseif ($approvals->where('status', 'approved')->count() == $no_of_approvers && $no_of_approvers > 0) {
                    $overallStatus = 'Approved';
                } elseif ($approvals->where('status', 'approved')->isNotEmpty()) {
                    $overallStatus = 'Partially Approved';
                } elseif ($approvals->where('status', 'review')->isNotEmpty()) {
                    $overallStatus = 'In Review';
                } else {
                    $overallStatus = 'Pending';
                }
                return $overallStatus;
            })
            ->addColumn('actions', function ($leave) {
                return $leave->action_buttons;
            })
            ->make(true);
    }
}
