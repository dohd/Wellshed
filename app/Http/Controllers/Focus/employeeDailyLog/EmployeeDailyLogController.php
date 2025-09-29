<?php

namespace App\Http\Controllers\Focus\employeeDailyLog;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\stockIssuance\StockIssuanceRequestController;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\Permission\Permission;
use App\Models\Access\User\User;
use App\Models\Company\Company;
use App\Models\department\Department;
use App\Models\employeeDailyLog\EdlSubcategoryAllocation;
use App\Models\employeeDailyLog\EmployeeDailyLog;
use App\Models\employeeDailyLog\EmployeeTaskSubcategories;
use App\Models\employeeDailyLog\EmployeeTasks;
use App\Models\financialYear\FinancialYear;
use App\Models\hrm\Hrm;
use App\Models\hrm\HrmMeta;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Response;
use Yajra\DataTables\Facades\DataTables;

class EmployeeDailyLogController extends Controller
{
    protected $total_performance = 0;
    protected $total_tasks = 0;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    public function index(Request $request)
    {

        if (Auth::user()->hasPermission('manage-daily-logs') === false) {
            return redirect()->back();
        }

        $edlEmployeeIds = EmployeeDailyLog::all()->pluck('employee')->unique()->values();
        $edlDates = Auth::user()->hasPermission('review-daily-logs') ?
            EmployeeDailyLog::all()->pluck('date')->unique()->values() :
            EmployeeDailyLog::where('employee', Auth::user()->id)->pluck('date')->unique()->values();

        $edlDates = $edlDates->toArray();

        usort($edlDates, function ($a, $b) {
            return strtotime($b) - strtotime($a);
        });

        $edlYears = [];
        foreach ($edlDates as $yr) {

            $yearValue = (new DateTime($yr))->format('Y');
            array_push($edlYears, $yearValue);
        }
        $edlYears = array_unique($edlYears);

        $employees = [];
        foreach ($edlEmployeeIds as $id) {

            $employeeDetails = User::where('id', $id)
                ->where('status', 1)
                ->select(
                    'id',
                    DB::raw('CONCAT(first_name, " ", last_name) as full_name')
                )
                ->get();

            if (count($employeeDetails) > 0) array_push($employees, $employeeDetails[0]->toArray());
        }

        $employees = collect($employees)->sortBy('full_name')->values()->all();

        $months = [
            ['label' => 'January', 'value' => 1],
            ['label' => 'February', 'value' => 2],
            ['label' => 'March', 'value' => 3],
            ['label' => 'April', 'value' => 4],
            ['label' => 'May', 'value' => 5],
            ['label' => 'June', 'value' => 6],
            ['label' => 'July', 'value' => 7],
            ['label' => 'August', 'value' => 8],
            ['label' => 'September', 'value' => 9],
            ['label' => 'October', 'value' => 10],
            ['label' => 'November', 'value' => 11],
            ['label' => 'December', 'value' => 12],
        ];


        if ($request->ajax()) {

            if (Auth::user()->hasPermission('review-daily-logs')) {

                $employeeDailyLog = EmployeeDailyLog::join('users', 'employee_daily_logs.employee', '=', 'users.id')
                    ->select(
                        'employee_daily_logs.employee as employee_id',
                        'edl_number',
                        'date',
                        DB::raw('CONCAT(first_name, " ", last_name) AS employee'),
                        'rating',
                        'remarks'

                    )
                    ->whereHas('tasks') // Ensure logs with tasks are fetched
                    ->with('tasks');

                $employeeDailyLog->when(!empty($request->employee), function ($query) use ($request) {
                    $query->where('employee', request('employee'));
                });
            } else {

                $employeeDailyLog = EmployeeDailyLog::where('employee', Auth::user()->id)
                    ->join('users', 'employee_daily_logs.employee', '=', 'users.id')
                    ->select(
                        'employee_daily_logs.employee as employee_id',
                        'edl_number',
                        'date',
                        DB::raw('CONCAT(first_name, " ", last_name) AS employee'),
                        'rating',
                        'remarks'

                    )
                    ->whereHas('tasks') // Ensure logs with tasks are fetched
                    ->with('tasks');
            }

            $employeeDailyLog->when(!empty($request->date), function ($query) use ($request) {
                $query->whereDate('date', (new DateTime(request('date')))->format('Y-m-d'));
            });

            $employeeDailyLog->when(!empty($request->month), function ($query) use ($request) {
                $query->whereMonth('date', request('month'));
            });

            $employeeDailyLog->when(!empty($request->year), function ($query) use ($request) {
                $query->whereYear('date', request('year'));
            });

            $logs = $employeeDailyLog->get();

            // Calculate the total sum of task hours
            $totalTaskHours = $logs->sum(function ($log) {
                return $log->tasks->sum('hours');
            });
            $total_performance = $logs->sum(function ($log) {
                $taskCount = $log->tasks->count();
                $sum_of_performance = $log->tasks->sum('work_done');
                $average_percent = $sum_of_performance / $taskCount;
                $this->total_performance += $sum_of_performance;
                $this->total_tasks += $taskCount;
                return $sum_of_performance;
            });
            $taskInstances = $logs->sum(function ($log) {
                return $log->tasks->count();
            });


            $edlCollection = [];
            // Filter by customer-login
            if (auth()->user()->customer_id) {
                $edlCollection = $employeeDailyLog
                    ->get()
                    ->filter(function($item) {
                        $emp_allocation = EdlSubcategoryAllocation::where('employee', $item->employee_id)->first();
                        if ($emp_allocation->customer_id == auth()->user()->customer_id) return true;
                        return false;
                    });

            } else {
                $edlCollection = $employeeDailyLog->get();
            }

            return Datatables::of($edlCollection)
                ->addIndexColumn()
                ->editColumn('employee', function ($edl) {

                    $position = HrmMeta::where('user_id', $edl->employee_id)->first()->position;

                    return '<p>' . $edl->employee . '</p> ' .
                        '<small>' . $position . '</small> ';
                })
                ->addColumn('action', function ($model) {

                    $view = ' <a href="' . route('biller.employee-daily-log.show', $model->edl_number) . '" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="View"><i  class="fa fa-eye"></i></a> ';
                    $edit = ' <a href="' . route('biller.employee-daily-log.edit', $model->edl_number) . '" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="fa fa-pencil"></i></a> ';
                    $delete = '<a href="' . route('biller.employee-daily-log.destroy', $model->edl_number) . '" 
                            class="btn btn-danger round" data-method="delete"
                            data-trans-button-cancel="' . trans('buttons.general.cancel') . '"
                            data-trans-button-confirm="' . trans('buttons.general.crud.delete') . '"
                            data-trans-title="' . trans('strings.backend.general.are_you_sure') . '" 
                            data-toggle="tooltip" 
                            data-placement="top" 
                            title="Delete"
                            >
                                <i  class="fa fa-trash"></i>
                            </a>';

                    $edit = Auth::user()->id === $model->employee_id ? $edit : '';
                    $delete = Auth::user()->id === $model->employee_id ? $delete : '';


                    if (Auth::user()->hasPermission('review-daily-logs') === true) {

                        $review = '<a href="' . route('biller.edl-remark', $model->edl_number) . '" class="btn btn-bitbucket mr-1 round">Review</a>';

                        return $review . $view . $edit . $delete;
                    } else {

                        return $view . $edit . $delete;
                    }
                })
                ->addColumn('tasks', function ($model) {
                    $taskCount = count($model->tasks);

                    return $taskCount;
                })
                ->addColumn('hours', function ($model) {

                    $employeeTasks = $model->tasks;
                    $hours = 0;

                    foreach ($employeeTasks as $task) {
                        $hours += $task['hours'];
                    }

                    return $hours;
                })
                ->addColumn('average_percent_performance', function ($model) {
                    $taskCount = count($model->tasks);
                    $sum_of_performance = $model->tasks->sum('work_done');
                    $average_percent = $sum_of_performance / $taskCount;
                    // $this->total_performance += $sum_of_performance;
                    // $this->total_tasks += $taskCount;

                    return number_format($average_percent, 2);
                })
                ->addColumn('total_hours', function ($model) use ($totalTaskHours) {
                    return numberFormat($totalTaskHours);
                })
                ->addColumn('rating', function ($model) {
                    $rate = '';
                    $ratings = [
                        '0' => '0 - Rework/ Repetition/ No Attempt',
                        '1' => '1 - Minimal Attempt',
                        '2' => '2 - Partially done /Few worked hours',
                        '3' =>   '3 - Done but not to Expectations/Longer time',
                        '4' => '4 - Well done and within perceived timelines',
                        '5' => '5 - Exceeds Expectations/Extra mile',
                    ];
                    $modelRating = $model->rating === null ? '' : $model->rating;
                    // Match the rating with the key in the $ratings array
                    foreach ($ratings as $k => $rating) {
                        if ((string)$k == (string)$modelRating) {
                            $rate = $rating;
                            break;  // Exit loop once matched
                        }
                    }

                    return $rate;
                })
                ->addColumn('total_performance', function ($model) {
                    $average = $this->total_performance / $this->total_tasks;
                    return numberFormat($average);
                })
                ->addColumn('customer', function ($model) {
                    $emp_allocation = EdlSubcategoryAllocation::where('employee', $model->employee_id)->first();
                    $customer = $emp_allocation->customer ? $emp_allocation->customer->company : '';
                    return $customer;
                })
                ->addColumn('branch', function ($model) {
                    $emp_allocation = EdlSubcategoryAllocation::where('employee', $model->employee_id)->first();
                    $branch = $emp_allocation->branch ? $emp_allocation->branch->name : '';
                    return $branch;
                })
                ->rawColumns(['action', 'employee', 'remarks','customer','branch'])
                ->make(true);
        }


        $isReviewer = Auth::user()->hasPermission('review-daily-logs');

        $edlMetrics = $this->edlDashboard();

        return new ViewResponse('focus.employeeDailyLog.index', compact('isReviewer', 'employees', 'edlDates', 'months', 'edlYears', 'edlMetrics'));
    }


    public function edlDashboard()
    {

        $yesterday = (new DateTime('now'))->sub(new DateInterval('P1D'))->format('Y-m-d');
        //Logs Filled Today
        $filledYesterday = EmployeeDailyLog::where('date', $yesterday)->get()->count();

        //Logs not Filled Today
        $employees = Hrm::all();
        $noOfLoggers = 0;
        foreach ($employees as $emp) {

            $user = User::where('id', $emp['id'])->first();

            if ($user && $user->hasPermission('create-daily-logs')) {
                $noOfLoggers++;
            }
        }
        $notFilledYesterday = $noOfLoggers - $filledYesterday;

        //Hours Logged today
        $tasksLoggedYesterday = 0;
        $hoursLoggedYesterday = 0;
        $yesterdayLogs = EmployeeDailyLog::where('date', $yesterday)->get();
        foreach ($yesterdayLogs as $log) {

            $edlTasks = EmployeeDailyLog::where('edl_number', $log['edl_number'])->first()->tasks;

            $tasksLoggedYesterday += $edlTasks->count();

            foreach ($edlTasks as $task) {

                $hoursLoggedYesterday += $task['hours'];
            }
        }

        $yesterdayLogs = EmployeeDailyLog::where('date', $yesterday)->get();
        $yesterdayUnreviewedLogs = 0;

        foreach ($yesterdayLogs as $log) {

            if (empty($log['rating']) && empty($log['remarks'])) {
                $yesterdayUnreviewedLogs++;
            }
        }


        return compact('filledYesterday', 'notFilledYesterday', 'tasksLoggedYesterday', 'hoursLoggedYesterday', 'yesterdayUnreviewedLogs');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return ViewResponse
     */
    public function create()
    {
        if (Auth::user()->hasPermission('create-daily-logs') === false) {
            return redirect()->back();
        }

        $departmentId = HrmMeta::where('user_id', Auth::user()->id)->first()->department_id;

        $taskCats = EmployeeTaskSubcategories::where('department', $departmentId)->get();

        $taskCategories = $this->getTaskCategories();

        //        return $taskCategories;

        return new ViewResponse('focus.employeeDailyLog.create', compact('taskCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => [
                'required',
                'after_or_equal:' . (new DateTime('now'))->sub(new DateInterval('P28D'))->format('Y-m-d'),
                'before_or_equal:' . (new DateTime('now'))->format('Y-m-d'),
            ],
            'subcategory0' => ['required', 'string', 'max:1000'],
            'hours0' => ['required', 'numeric'],
            'description0' => ['required', 'string', 'max:1000'],
            'performance0' => ['required', 'numeric'],
            'work_done0' => ['required', 'numeric'],
        ]);



        if (Auth::user()->hasPermission('create-daily-logs') === false) {
            return redirect()->back();
        }

        //        $today = new DateTime('now');
        //        $logDate = new DateTime($validated['date']);
        //
        //        $dateDiff = ($today->diff($logDate))->days;
        //
        //        if ($dateDiff > 7) {
        //            return redirect()->back()->with('flash_error', 'You can Create Logs for only the Past 1 week');
        //        }

        if (!empty(EmployeeDailyLog::where('employee', Auth::user()->id)->where('date', (new DateTime($validated['date']))->format('Y-m-d'))->first())) {

            return redirect()->back()->with('flash_error', 'You Already Created an EDL for the Date: ' . (new DateTime($validated['date']))->format('D, d M Y'));
        }


        try {
            DB::beginTransaction();

            $employeeDailyLog = new EmployeeDailyLog;

            $employeeDailyLog->edl_number = 'EDL-' . Auth::user()->id . '-' . strtoupper(Str::random(4));
            $employeeDailyLog->fill($validated);

            $employeeDailyLog->date = (new DateTime($validated['date']))->format('Y-m-d');

            $employeeDailyLog->employee =  Auth::user()->id;

            $employeeDailyLog->save();


            //            return ['egg' => EmployeeTaskSubcategories::where('name', $request['category0'])->first()->id];

            for ($i = 0; $i < 20; $i++) {

                if (!empty($request['subcategory' . $i]) && !empty($request['hours' . $i]) && !empty($request['description' . $i])) {

                    $employeeTask = new EmployeeTasks();

                    $employeeTask->et_number = uniqid('ET' . Auth::user()->id . '-');

                    $employeeTask->edl_number = $employeeDailyLog->edl_number;
                    $employeeTask->category = HrmMeta::where('user_id', Auth::user()->id)->first()->department_id;
                    $employeeTask->subcategory = $request['subcategory' . $i];

                    $employeeTask->hours = $request['hours' . $i];
                    $employeeTask->description = $request['description' . $i];
                    $employeeTask->performance = $request['performance' . $i];
                    $employeeTask->work_done = $request['work_done' . $i];

                    $employeeTask->save();
                }
            }

            $empTasks = $employeeDailyLog->tasks;
            $hours = 0;

            foreach ($empTasks as $task) {
                $hours += $task['hours'];
            }

            if ($hours > 15) {
                DB::rollBack();
                return redirect()->back()->with('flash_error', 'Total Hours For Your Daily Log Cannot Exceed 14 Hours.');
            } else {
                DB::commit();
            }
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'SQL ERROR : ' . $e->getMessage());
        }

        return new RedirectResponse(route('biller.employee-daily-log.index'), ['flash_success' => 'Daily Log Saved Successfully!']);
    }

    /**
     * Displays the EDL Review page
     * @param $edlNumber
     * @return ViewResponse|\Illuminate\Http\RedirectResponse
     */
    public function makeLogRemark($edlNumber)
    {

        if (Auth::user()->hasPermission('review-daily-logs') === false) {
            return redirect()->back();
        }

        $edl = EmployeeDailyLog::where('edl_number', $edlNumber)
            ->join('users', 'employee_daily_logs.employee', '=', 'users.id')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->select(
                'edl_number',
                'date',
                DB::raw('CONCAT(first_name, " ", last_name) AS employee'),
                'roles.name as role',
                'rating',
                'remarks',
            )
            ->first();


        $edlTasks = EmployeeTasks::where('edl_number', $edlNumber)
            ->join('departments', 'employee_tasks.category', '=', 'departments.id')
            ->join('employee_task_subcategories', 'employee_tasks.subcategory', '=', 'employee_task_subcategories.id')
            ->select(
                'et_number',
                'employee_task_subcategories.name as subcategory',
                'employee_task_subcategories.frequency as frequency',
                'hours',
                'frequency',
                'description',
                'performance',
                'work_done',
                'target',
                'uom'
            )
            ->get();

        $totalHours = array_sum((new StockIssuanceRequestController())->getValuesByKey($edlTasks->toArray(), 'hours'));
        // dd($edl);

        $data = [
            "edl" => $edl,
            "edlTasks" => $edlTasks,
        ];

        $ratings = [

            '0' => '0 - Rework/ Repetition/ No Attempt',
            '1' => '1 - Minimal Attempt',
            '2' => '2 - Partially done /Few worked hours',
            '3' =>   '3 - Done but not to Expectations/Longer time',
            '4' => '4 - Well done and within perceived timelines',
            '5' => '5 - Exceeds Expectations/Extra mile',
        ];

        //        return compact('data');

        return new ViewResponse('focus.employeeDailyLog.logRemark', compact('data', 'edlNumber', 'ratings', 'totalHours'));
    }


    /**
     * Saves the rating and remark of the EDL
     * @param Request $request
     * @param $edlNumber
     * @return RedirectResponse|\Illuminate\Http\RedirectResponse
     */
    public function storeLogRemark(Request $request, $edlNumber)
    {

        if (Auth::user()->hasPermission('review-daily-logs') === false) {
            return redirect()->back();
        }

        $validated = $request->validate([
            'rating' => ['required', 'string'],
            'remarks' => ['required', 'string', 'max:1000'],
        ]);

        try {
            DB::beginTransaction();

            $edl = EmployeeDailyLog::where('edl_number', $edlNumber)->first();

            $edl->fill($validated);

            $edl->reviewer = Auth::user()->id;
            $edl->reviewed_at = (new DateTime('now'))->format('D, d M Y, H:i:s');

            $edl->save();


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('flash_error', $e->getMessage());
        }

        return new RedirectResponse(route('biller.employee-daily-log.index'), ['flash_success' => 'Daily Log Review Saved Successfully!']);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($edlNumber)
    {
        $this->checkRights(Auth::user()->can('create-daily-logs'));

        $sql = "
                SELECT rose_employee_daily_logs.edl_number,
                       rose_employee_daily_logs.date,
                       CONCAT(loggers.first_name, ' ', loggers.last_name) AS employee,
                       rose_roles.name AS role,
                       rose_employee_daily_logs.rating,
                       rose_employee_daily_logs.remarks,
                       CONCAT(reviewers.first_name, ' ', reviewers.last_name) AS reviewer,
                       rose_employee_daily_logs.reviewed_at
                FROM rose_employee_daily_logs
                JOIN rose_users AS loggers ON rose_employee_daily_logs.employee = loggers.id
                LEFT JOIN rose_users AS reviewers ON rose_employee_daily_logs.reviewer = reviewers.id
                JOIN rose_role_user ON loggers.id = rose_role_user.user_id -- Use loggers.id instead of rose_users.id
                JOIN rose_roles ON rose_role_user.role_id = rose_roles.id
                WHERE rose_employee_daily_logs.edl_number = :edlNumber
            ";

        $edl = DB::select($sql, ['edlNumber' => $edlNumber]);
        //Converting from stdClass to Array
        $edl = json_decode(json_encode($edl[0], true), true);

        $edlTasks = EmployeeTasks::where('edl_number', $edlNumber)
            ->join('departments', 'employee_tasks.category', '=', 'departments.id')
            ->join('employee_task_subcategories', 'employee_tasks.subcategory', '=', 'employee_task_subcategories.id')
            ->leftJoin('key_activities', 'employee_task_subcategories.key_activity_id', '=', 'key_activities.id')
            ->select(
                'et_number',
                'departments.name as category',
                'employee_task_subcategories.name as subcategory',
                'employee_task_subcategories.frequency as frequency',
                'hours',
                'employee_tasks.description as description',
                'performance',
                'work_done',
                \DB::raw('COALESCE(rose_employee_task_subcategories.key_activities, rose_key_activities.name) as key_activities'),
                'employee_task_subcategories.uom as uom',
                'employee_task_subcategories.target as target',
            )
            ->get();

        $totalHours = array_sum((new StockIssuanceRequestController())->getValuesByKey($edlTasks->toArray(), 'hours'));

        return new ViewResponse('focus.employeeDailyLog.show', compact('edl', 'edlTasks', 'edlNumber', 'totalHours'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return ViewResponse
     * @throws Exception
     */
    public function edit($edlNumber)
    {

        if (Auth::user()->hasPermission('edit-daily-logs') === false) {
            return redirect()->back();
        }

        $edl = EmployeeDailyLog::where('edl_number', $edlNumber)->first();

        if ($edl->employee !== Auth::user()->id) {
            return redirect()->back();
        }

        if (!empty($edl->rating) && !empty($edl->remarks)) {
            return redirect()->back()->with('flash_error', 'Cannot edit this Daily Log as it has already been reviewed');
        }


        //        $today = new DateTime('now');
        //        $logDate = new DateTime($edl->created_at);
        //        $dateDiff = ($today->diff($logDate))->days;
        //
        //        if ($dateDiff > 1 && !Auth::user()->hasRole('Directorate')) {
        //            return redirect()->back()->with('flash_error', 'You can Edit Logs for Only One Day After the Initial Posting');
        //        }


        $edl = EmployeeDailyLog::where('edl_number', $edlNumber)
            ->join('users', 'employee_daily_logs.employee', '=', 'users.id')
            ->select(
                'employee_daily_logs.employee as employee_id',
                'edl_number',
                'date',
                DB::raw('CONCAT(first_name, " ", last_name) AS employee'),
            )
            ->get();


        $edlTasks = EmployeeTasks::where('edl_number', $edlNumber)
            ->join('departments', 'employee_tasks.category', '=', 'departments.id')
            ->join('employee_task_subcategories', 'employee_tasks.subcategory', '=', 'employee_task_subcategories.id')
            ->leftJoin('key_activities', 'employee_task_subcategories.key_activity_id', '=', 'key_activities.id')
            ->select(
                'et_number',
                'subcategory',
                'hours',
                'employee_tasks.description as description',
                'performance',
                'work_done',
                \DB::raw('COALESCE(rose_employee_task_subcategories.key_activities, rose_key_activities.name) as key_activities'),
                'employee_task_subcategories.uom as uom',
                'employee_task_subcategories.target as target',
                'employee_task_subcategories.frequency as frequency',
            )
            ->get();

        $data = [
            "edl" => $edl,
            "edlTasks" => $edlTasks,
        ];

        $employeeId =
            $taskCategories = Auth::user()->hasRole('Directorate') ? $this->getTaskCategories($edl[0]['employee_id']) : $this->getTaskCategories();

        return new ViewResponse('focus.employeeDailyLog.edit', compact('data', 'edlNumber', 'taskCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $edlNumber)
    {
        if (Auth::user()->hasPermission('edit-daily-logs') === false) {
            return redirect()->back();
        }

        $edl = EmployeeDailyLog::where('edl_number', $edlNumber)->first();
        if ($edl->employee !== Auth::user()->id) {
            return redirect()->back();
        }


        try {
            DB::beginTransaction();

            //            $edl->date = (new DateTime($request->date))->format('Y-m-d');
            //
            //            $edl->save();

            $etNumbers = EmployeeTasks::where('edl_number', $edlNumber)->pluck('et_number');


            foreach ($etNumbers as $etNo) {

                $et = EmployeeTasks::where('et_number', $etNo)->first();

                if (empty($request['subcategory' . $etNo]) && empty($request['hours' . $etNo]) && empty($request['description' . $etNo])) {

                    $et->delete();
                } else {

                    $et->subcategory = $request['subcategory' . $etNo];
                    $et->hours = $request['hours' . $etNo];
                    $et->description = $request['description' . $etNo];
                    $et->performance = $request['performance' . $etNo];
                    $et->work_done = $request['work_done' . $etNo];

                    $et->save();
                }
            }

            for ($i = 0; $i < 20; $i++) {

                if (!empty($request['subcategory' . $i])) {

                    $employeeTask = new EmployeeTasks();

                    $employeeTask->et_number = uniqid('ET' . Auth::user()->id . '-');

                    $employeeTask->edl_number = $edl->edl_number;
                    $employeeTask->category = HrmMeta::where('user_id', Auth::user()->id)->first()->department_id;
                    $employeeTask->subcategory = $request['subcategory' . $i];

                    $employeeTask->hours = $request['hours' . $i];
                    $employeeTask->description = $request['description' . $i];
                    $employeeTask->performance = $request['performance' . $i];
                    $employeeTask->work_done = $request['work_done' . $i];

                    $employeeTask->save();
                }
            }

            $empTasks = $edl->tasks;
            $hours = 0;

            foreach ($empTasks as $task) {
                $hours += $task['hours'];
            }

            if ($hours > 15) {
                DB::rollBack();
                return redirect()->back()->with('flash_error', 'Total Hours For Your Daily Log Cannot Exceed 14 Hours.');
            } else {
                DB::commit();
            }
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('flash_error', $e->getMessage());
        }


        return new RedirectResponse(route('biller.employee-daily-log.show', $edlNumber), ['flash_success' => 'Daily Log Review Updated Successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($edlNumber)
    {
        if (Auth::user()->hasPermission('delete-daily-logs') === false) {
            return redirect()->back();
        }

        $edl = EmployeeDailyLog::where('edl_number', $edlNumber)->first();

        if ($edl->employee !== Auth::user()->id) {
            return redirect()->back();
        }

        try {
            DB::beginTransaction();


            if ((empty($edl->remark) && empty($edl->rating)) || Auth::user()->hasRole('Directorate')) {

                $edlTasks = $edl->tasks;

                foreach ($edlTasks as $task) {

                    $et = EmployeeTasks::where('et_number', $task['et_number'])->first();
                    $et->delete();
                }

                $edl->delete();
            } else {
                return redirect()->back()->with('flash_error', 'Cannot Delete this EDL as it has already been reviewed...');
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('flash_error', $e->getMessage());
        }


        return new RedirectResponse(route('biller.employee-daily-log.index'), ['flash_success' => 'Daily Log Deleted Successfully!']);
    }


    /**
     * Checks if the user owns the resource
     * @param EmployeeDailyLog $edl
     * @return \Illuminate\Http\RedirectResponse|void
     */
    private function checkOwnership(EmployeeDailyLog $edl)
    {
        if ($edl->employee !== Auth::user()->id) {
            return redirect()->back();
        }
    }

    /**
     * Returna an array of task categories
     * @return array
     */
    private function getTaskCategories($employeeId = 'NA'): array
    {

        $employeeId = $employeeId === 'NA' ? Auth::user()->id : $employeeId;

        $departmentId = HrmMeta::where('user_id', $employeeId)->first()->department_id;
        $taskCategories = [];

        $edlSubcategoryAllocation = EdlSubcategoryAllocation::where('employee', $employeeId)->first();
        $allocations = [];

        if (!empty($edlSubcategoryAllocation)) {
            $allocations = json_decode($edlSubcategoryAllocation->allocations);
        }

        foreach ($allocations as $alloc) {

            $cat = EmployeeTaskSubcategories::where('id', $alloc)->first();

            $taskCategory = [
                'label' => $cat->name,
                'value' => $cat->id,
                'frequency' => $cat->frequency,
            ];

            array_push($taskCategories, $taskCategory);
        }

        return $taskCategories;
    }


    public function createPerms()
    {

        $rosePermissions = array(


            array('name' => 'view-stakeholders', 'display_name' => "Stakeholders View Permission", 'module_id' => 19),
            array('name' => 'create-stakeholders', 'display_name' => "Stakeholders Create Permission", 'module_id' => 19),
            array('name' => 'edit-stakeholders', 'display_name' => "Stakeholders Edit Permission", 'module_id' => 19),
            array('name' => 'delete-stakeholders', 'display_name' => "Stakeholders Delete Permission", 'module_id' => 19),

            array('name' => 'reclassify-purchases', 'display_name' => "Reclassify-Purchases Permission", 'module_id' => 19),


            array('name' => 'manage-approved-budgets', 'display_name' => "Approved-Budgets Manage Permission", 'module_id' => 19),
            array('name' => 'print-technicians-approved-budgets', 'display_name' => "Approved-Budgets Print Technicians Permission", 'module_id' => 19),
            array('name' => 'print-stores-approved-budgets', 'display_name' => "Approved-Budgets Print Stores Permission", 'module_id' => 19),



            array('name' => 'manage-promo-codes', 'display_name' => "Affinity-Program | Promo Codes Manage Permission", 'module_id' => 19),
            array('name' => 'create-promo-codes', 'display_name' => "Affinity-Program | Promo Codes Create Permission", 'module_id' => 19),
            array('name' => 'edit-promo-codes', 'display_name' => "Affinity-Program | Promo Codes Edit Permission", 'module_id' => 19),
            array('name' => 'delete-promo-codes', 'display_name' => "Affinity-Program | Promo Codes Delete Permission", 'module_id' => 19),


            array('name' => 'manage-reserve-promo-codes', 'display_name' => "Affinity-Program | Promo Codes Reservations Manage Permission", 'module_id' => 19),
            array('name' => 'create-customer-reservation', 'display_name' => "Affinity-Program | Customer Reservations Create Permission", 'module_id' => 19),
            array('name' => 'edit-customer-reservation', 'display_name' => "Affinity-Program | Customer Reservations Edit Permission", 'module_id' => 19),
            array('name' => 'create-3p-reservation', 'display_name' => "Affinity-Program | Third Party Reservations Create Permission", 'module_id' => 19),
            array('name' => 'edit-3p-reservation', 'display_name' => "Affinity-Program | Third Party Reservations Edit Permission", 'module_id' => 19),
            array('name' => 'edit-referral-reservation', 'display_name' => "Affinity-Program | Referral Reservations Edit Permission", 'module_id' => 19),


            array('name' => 'dashboard-visualizations-recent-ai-leads', 'display_name' => "Dashboard-Visualization | Recent A.I Leads Permission", 'module_id' => 19),
            array('name' => 'dashboard-visualizations-recent-ai-transcripts', 'display_name' => "Dashboard-Visualization | Recent A.I Chat Transcripts Permission", 'module_id' => 19),
            array('name' => 'dashboard-visualizations-recent-leads', 'display_name' => "Dashboard-Visualization | Recent Leads Permission", 'module_id' => 19),
            array('name' => 'dashboard-visualizations-quotes', 'display_name' => "Dashboard-Visualization | Recent Quotes Permission", 'module_id' => 19),



        );

        foreach ($rosePermissions as $perm) {

            Permission::create([
                'name' => $perm['name'],
                'display_name' => $perm['display_name'],
            ]);
        }


        return Permission::all();
    }


    private function checkRights($canDoIt)
    {

        if ($canDoIt === false) {
            return redirect()->back();
        }
    }

    public function index_kpis()
    {
        //Financial year
        $financial_years = FinancialYear::orderBy('id', 'DESC')->get(['id', 'name']);
        //Employees
        $permission = 'review-daily-logs';
        $users = collect([Hrm::find(auth()->user()->id)]);
        if (access()->allow($permission)) {
            $users = Hrm::all();
        }


        //Frequencies
        $frequencies = ["Daily", "Weekly", "Bi-Weekly", "Monthly", "Quarterly", "Semi-Annually", "Annual"];
        //
        $months = [
            ['label' => 'January', 'value' => 1],
            ['label' => 'February', 'value' => 2],
            ['label' => 'March', 'value' => 3],
            ['label' => 'April', 'value' => 4],
            ['label' => 'May', 'value' => 5],
            ['label' => 'June', 'value' => 6],
            ['label' => 'July', 'value' => 7],
            ['label' => 'August', 'value' => 8],
            ['label' => 'September', 'value' => 9],
            ['label' => 'October', 'value' => 10],
            ['label' => 'November', 'value' => 11],
            ['label' => 'December', 'value' => 12],
        ];

        return view('focus.employeeDailyLog.index_kpis', compact('frequencies', 'financial_years', 'users', 'months'));
    }

    public function get_kpis()
    {
        $selectedFrequency = request('frequency') ?? "Annual";  // Frequency filter
        $first_year = FinancialYear::where('ins', auth()->user()->ins)->first();
        $year_id = request('financial_year_id') ?? $first_year->id;           // Financial year filter
        $selectedMonth = request('month') ?? now()->format('m'); // Month filter
        $employeeId = request('user_id') ?? auth()->user()->id;    // Employee filter (user ID)
        $financialYear = FinancialYear::where('id', $year_id)->first();
        $startDate = $financialYear->start_date;
        $endDate = $financialYear->end_date;

        $holidays = DB::table('leave_holidays')->pluck('date')->toArray();
        $holiday = $this->adjustHolidays($holidays);
        // dd($holiday);

        // Set the month range
        $monthStartDate = Carbon::parse($startDate)->month($selectedMonth)->startOfMonth();
        $monthEndDate = Carbon::parse($startDate)->month($selectedMonth)->endOfMonth();
        // dd($holiday, $monthStartDate, $monthEndDate);
        $period = CarbonPeriod::create($monthStartDate, $monthEndDate);

        // Filter out Sundays and holidays
        $validDates = collect($period)->filter(function ($date) use ($holiday) {
            return !$date->isSunday() && !in_array($date->toDateString(), $holiday);
        })->values();

        // Return the result as an array of dates (strings)
        $allDates = $validDates->map(fn($date) => $date->toDateString())->toArray();

        $employee = Hrm::find($employeeId);
        $fullname = $employee->fullname;


        // Retrieve the allocations for the specific employee
        $allocation = EdlSubcategoryAllocation::where('employee', $employeeId)->first();
        $allocatedTaskIds = $allocation ? json_decode($allocation->allocations, true) : [];

        $tasks = EmployeeTaskSubcategories::when($selectedFrequency, function ($query) use ($selectedFrequency) {
            return $query->where('frequency', $selectedFrequency);
        })
            ->whereIn('id', $allocatedTaskIds)
            ->with(['employeeTasks.employeeDailyLog' => function ($query) use ($employeeId, $monthStartDate, $monthEndDate, $selectedMonth) {
                // Filtering employee daily logs by the specific employee's ID
                $query->where('employee', $employeeId)
                    // ->whereMonth('date', $selectedMonth)
                    ->whereBetween('date', [$monthStartDate, $monthEndDate]);
            }])
            ->get()
            ->map(function ($q) use ($allDates, $fullname, $employeeId, $selectedFrequency, $monthStartDate, $monthEndDate) {

                // Retrieve and filter employee tasks for this subcategory
                $filteredTasks = $q->employeeTasks->filter(function ($task) use ($employeeId, $monthStartDate, $monthEndDate) {
                    // Extract employeeId from edl_number in the format EDL-155-PMKF

                    if (preg_match('/^EDL-(\d+)-/', $task->edl_number, $matches)) {
                        if ($task->employeeDailyLog) { // Check if employeeDailyLog is not null
                            // Retrieve daily logs for this task and check if there are any within the date range
                            $dailyLogs = $task->employeeDailyLog->whereBetween('date', [$monthStartDate, $monthEndDate]);
                            // dd($dailyLogs->get());
                            return $matches[1] == $employeeId && $dailyLogs->get()->isNotEmpty(); // Check if daily logs exist
                        }
                    }
                    return false;
                });

                // Calculate expected count based on the frequency
                $totalDaysInMonth = Carbon::now()->daysInMonth;

                switch ($selectedFrequency) {
                    case 'Daily':
                        $expectedCount = count($allDates); // Number of days in the current month
                        break;
                    case 'Weekly':
                        $expectedCount = ceil($totalDaysInMonth / 7); // Approximate weekly occurrences
                        break;
                    case 'Bi-Weekly':
                        $expectedCount = ceil($totalDaysInMonth / 14); // Approximate bi-weekly occurrences
                        break;
                    case 'Monthly':
                        $expectedCount = 1; // Once a month
                        break;
                    case 'Quarterly':
                        $expectedCount = 1 / 3; // Once every three months (adjust based on yearly)
                        break;
                    case 'Semi-Annually':
                        $expectedCount = 1 / 6; // Once every six months (adjust based on yearly)
                        break;
                    case 'Annual':
                        $expectedCount = 1; // Once a year
                        break;
                    default:
                        $expectedCount = $totalDaysInMonth;
                        break;
                }
                // printlog($filteredTasks);


                // Calculate the number of daily logs recorded for this subcategory (task)
                $q->task_no = $filteredTasks
                    ->where('subcategory', $q->id)
                    ->count() . ' / ' . $expectedCount;

                // Set additional fields for the output
                $q->task_name = $q->name;
                $q->key_activities = $q->key_activity ? $q->key_activity->name : $q->key_activities;
                $q->user_name = $fullname ?? 'N/A';

                return $q;
            });

        // $totalTasksDone = $this->total_tasks($monthStartDate, $monthEndDate, $allocatedTaskIds, $employeeId, $allDates);
        // printlog($totalTasksDone);

        return DataTables::of($tasks)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('task_per_frequency', function ($task) {
                // dd($task['date']);
                return $task['task_no'];
            })
            ->addColumn('key_activities', function ($task) {
                return $task['key_activities'];
            })
            ->addColumn('task_name', function ($task) {
                return $task['task_name'];
            })
            ->addColumn('user_name', function ($task) {
                return $task['user_name'];
            })

            ->make(true);
    }


    public function total_tasks($monthStartDate, $monthEndDate, $allocatedTaskIds, $employeeId, $allDates)
    {
        $frequencies = ["Daily", "Weekly", "Bi-Weekly", "Monthly", "Quarterly", "Semi-Annually", "Annual"];
        $totalTasksDone = 0;
        $totalExpectedCount = 0;

        foreach ($frequencies as $frequency) {
            // Fetch tasks for the current frequency
            $tasks = EmployeeTaskSubcategories::when($frequency, function ($query) use ($frequency) {
                return $query->where('frequency', $frequency);
            })
                ->whereIn('id', $allocatedTaskIds)
                ->with(['employeeTasks.employeeDailyLog' => function ($query) use ($employeeId, $monthStartDate, $monthEndDate) {
                    // Filtering employee daily logs by the specific employee's ID and month
                    $query->where('employee', $employeeId)
                        ->whereBetween('date', [$monthStartDate, $monthEndDate]);
                }])
                ->get()
                ->map(function ($q) use ($allDates, $employeeId, $frequency, $monthStartDate, $monthEndDate) {

                    // Filter tasks within the date range and for the employee
                    $filteredTasks = $q->employeeTasks->filter(function ($task) use ($employeeId, $monthStartDate, $monthEndDate) {
                        if (preg_match('/^EDL-(\d+)-/', $task->edl_number, $matches)) {
                            if ($task->employeeDailyLog) {
                                $dailyLogs = $task->employeeDailyLog->whereBetween('date', [$monthStartDate, $monthEndDate]);
                                return $matches[1] == $employeeId && $dailyLogs->get()->isNotEmpty();
                            }
                        }
                        return false;
                    });

                    // Determine the expected count based on frequency
                    $totalDaysInMonth = Carbon::now()->daysInMonth;
                    switch ($frequency) {
                        case 'Daily':
                            $expectedCount = count($allDates);
                            break;
                        case 'Weekly':
                            $expectedCount = ceil($totalDaysInMonth / 7);
                            break;
                        case 'Bi-Weekly':
                            $expectedCount = ceil($totalDaysInMonth / 14);
                            break;
                        case 'Monthly':
                            $expectedCount = 1;
                            break;
                        case 'Quarterly':
                            $expectedCount = 1 / 3;
                            break;
                        case 'Semi-Annually':
                            $expectedCount = 1 / 6;
                            break;
                        case 'Annual':
                            $expectedCount = 1;
                            break;
                        default:
                            $expectedCount = $totalDaysInMonth;
                            break;
                    }

                    $q->task_no = $filteredTasks->where('subcategory', $q->id)->count() . ' / ' . $expectedCount;
                    return $q;
                });

            // Sum the completed tasks for this frequency
            $totalTasksDone += $tasks->sum(function ($task) {
                [$completedCount] = explode(' / ', $task->task_no);
                return (int) $completedCount;
            });
            $totalExpectedCount += $tasks->sum(function ($task) {
                [, $expectedCount] = explode(' / ', $task->task_no);
                return (float) $expectedCount; // Casting to float to handle fractional counts for frequencies like Quarterly
            });
        }

        return $totalTasksDone . '/' . $totalExpectedCount;
    }
    public function adjustHolidays($holidays)
    {
        $adjustedHolidays = [];
        foreach ($holidays as $holiday) {
            $holidayDate = Carbon::createFromFormat('Y-m-d', $holiday);
            // If the holiday falls on a Sunday, push it to Monday
            if ($holidayDate->isSunday()) {
                $holidayDate->addDay(); // Move to Monday
            }
            $adjustedHolidays[] = $holidayDate->format('Y-m-d');
        }
        return $adjustedHolidays;
    }

    public function kpi_summary_report($user_id, $month, $year, $financial_year_id)
    {
        $first_year = FinancialYear::withoutGlobalScopes()->where('id', $financial_year_id)->first();
        $year_id = $first_year->id;           // Financial year filter
        $selectedMonth = $month; // Month filter
        $employeeId = $user_id;    // Employee filter (user ID)
        $financialYear = FinancialYear::withoutGlobalScopes()->where('id', $year_id)->first();
        $startDate = $financialYear->start_date;
        $endDate = $financialYear->end_date;

        $holidays = DB::table('leave_holidays')->pluck('date')->toArray();
        $holiday = $this->adjustHolidays($holidays);
        // dd($holiday);

        // Set the month range
        $monthStartDate = Carbon::parse($startDate)->month($selectedMonth)->startOfMonth();
        $monthEndDate = Carbon::parse($startDate)->month($selectedMonth)->endOfMonth();
        // dd($holiday, $monthStartDate, $monthEndDate);
        $period = CarbonPeriod::create($monthStartDate, $monthEndDate);

        // Filter out Sundays and holidays
        $validDates = collect($period)->filter(function ($date) use ($holiday) {
            return !$date->isSunday() && !in_array($date->toDateString(), $holiday);
        })->values();

        // Return the result as an array of dates (strings)
        $allDates = $validDates->map(fn($date) => $date->toDateString())->toArray();

        $employee = Hrm::withoutGlobalScopes()->find($employeeId);
        $fullname = $employee->fullname;


        // Retrieve the allocations for the specific employee
        $allocation = EdlSubcategoryAllocation::withoutGlobalScopes()->where('employee', $employeeId)->first();
        $allocatedTaskIds = $allocation ? json_decode($allocation->allocations, true) : [];
        return $this->kpi_report($monthStartDate, $monthEndDate, $allocatedTaskIds, $employeeId, $allDates, $employee);
    }
    public function kpi_report($monthStartDate, $monthEndDate, $allocatedTaskIds, $employeeId, $allDates, $employee)
    {
        $frequencies = ["Daily", "Weekly", "Bi-Weekly", "Monthly", "Quarterly", "Semi-Annually", "Annual"];
        $totalTasksDone = 0;
        $totalExpectedCount = 0;
        $tasks = [];

        foreach ($frequencies as $frequency) {
            // Fetch tasks for the current frequency
            $tasks[] = EmployeeTaskSubcategories::withoutGlobalScopes()
                ->where('frequency', $frequency)
                ->whereIn('id', $allocatedTaskIds)
                ->with([
                    'employeeTasks' => function ($query) {
                        $query->withoutGlobalScopes(); // Remove global scopes for employeeTasks
                    },
                    'employeeTasks.employeeDailyLog' => function ($query) use ($employeeId, $monthStartDate, $monthEndDate) {
                        $query->withoutGlobalScopes() // Remove global scopes for employeeDailyLog
                            ->where('employee', $employeeId)
                            ->whereBetween('date', [$monthStartDate, $monthEndDate]);
                    }
                ])
                ->get()
                ->map(function ($q) use ($allDates, $employeeId, $frequency, $monthStartDate, $monthEndDate) {

                    // Filter tasks within the date range and for the employee
                    $filteredTasks = $q->employeeTasks->filter(function ($task) use ($employeeId, $monthStartDate, $monthEndDate) {
                        if (preg_match('/^EDL-(\d+)-/', $task->edl_number, $matches)) {
                            if ($task->employeeDailyLog) {
                                $dailyLogs = $task->employeeDailyLog()->withoutGlobalScopes()
                                    ->whereBetween('date', [$monthStartDate, $monthEndDate]);
                                return $matches[1] == $employeeId && $dailyLogs->get()->isNotEmpty();
                            }
                        }
                        return false;
                    });

                    // Determine the expected count based on frequency
                    $totalDaysInMonth = Carbon::now()->daysInMonth;
                    switch ($frequency) {
                        case 'Daily':
                            $expectedCount = count($allDates);
                            break;
                        case 'Weekly':
                            $expectedCount = ceil($totalDaysInMonth / 7);
                            break;
                        case 'Bi-Weekly':
                            $expectedCount = ceil($totalDaysInMonth / 14);
                            break;
                        case 'Monthly':
                            $expectedCount = 1;
                            break;
                        case 'Quarterly':
                            $expectedCount = 1 / 3;
                            break;
                        case 'Semi-Annually':
                            $expectedCount = 1 / 6;
                            break;
                        case 'Annual':
                            $expectedCount = 1;
                            break;
                        default:
                            $expectedCount = $totalDaysInMonth;
                            break;
                    }

                    $q->task_no = $filteredTasks->where('subcategory', $q->id)->count() . ' / ' . $expectedCount;
                    return $q;
                });

           
        }
        
        $headers = [
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $company = Company::find($employee->ins);
        // $new_date = DateTime::createFromFormat('Y-m', "$year-$month");
        // $formattedDate = $new_date->format('F Y'); 
      
        $data = [
            'tasks' => $tasks,
            'company' => $company,
            'employee' =>$employee->fullname,
        ];
        $html = view('focus.report.pdf.kpi_summary_report', ['data'=>$data])->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);

        return Response::stream($pdf->Output('purchaseorder.pdf', 'I'), 200, $headers);
    }
}
