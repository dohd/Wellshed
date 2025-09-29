<?php

namespace App\Http\Controllers\Focus\employeeAppraisal;

use App\Http\Controllers\Controller;
use App\Models\appraisal_type\AppraisalType;
use App\Models\attendance\Attendance;
use App\Models\Company\Company;
use App\Models\employeeAppraisal\EmployeeAppraisal;
use App\Models\employeeDailyLog\EdlSubcategoryAllocation;
use App\Models\employeeDailyLog\EmployeeTaskSubcategories;
use App\Models\hrm\Hrm;
use App\Models\workshift\WorkshiftItems;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EmployeeAppraisalController extends Controller
{
    // Display a listing of the resource.
    public function index(Request $request)
    {

        if (!access()->allow('manage-employee-appraisal')) return response("", 403);

        $appraisals = EmployeeAppraisal::all();
        $appraisal_types = AppraisalType::all();
        $employees = Hrm::select('id', 'first_name', 'last_name')->get();
        $ratings = [
            '1' => '1 - Poor',
            '2' => '2 - Below Average',
            '3' => '3 - Average',
            '4' => '4 - Good',
            '5' => '5 - Excellent'
        ];

        if ($request->ajax()) {

            $appraisals = EmployeeAppraisal::when(request('employeeFilter'), function ($q) {

                    $q->where('employee_id', intval(request('employeeFilter')));
                })
                ->when(request('employmentTypeFilter'), function ($q) {

                    $q->whereHas('employee', function ($q) {
                        $q->where('employment_type', request('employmentTypeFilter'));
                    });
                })
                ->when(request('appraisalTypeFilter'), function ($q) {

                    $q->where('appraisal_type_id', intval(request('appraisalTypeFilter')));
                })
                ->with(['employee_daily_logs.tasks', 'attendances'])
                ->get();
                $appraisals->each(function ($appraisal) {
                    $appraisal->employee_daily_logs = $appraisal->employee_daily_logs->filter(function ($log) use ($appraisal) {
                        return $log->date >= $appraisal->start_date && $log->date <= $appraisal->end_date;
                    });

                    $appraisal->absent_days = $appraisal->attendances
                    ->where('status', 'absent')
                    ->whereBetween('date',[$appraisal->start_date, $appraisal->end_date])->count();
                    $absent_hrs = $this->total_absent_days($appraisal);
                    // printlog($appraisal->absent_days);
                    $totalLatenessHours = $this->calculate_lateness($appraisal);
                    $total_unavailable_hours = $totalLatenessHours + $absent_hrs;
                    $appraisal->lateness = $totalLatenessHours;
                    $work_hours = $this->calculateWorkHours($appraisal->employee_id, $appraisal->start_date, $appraisal->end_date);
                    // printlog($work_hours);
                    $attendance_percent = $work_hours > 0 ? (($work_hours - $total_unavailable_hours) / $work_hours * 100) : 0;
                    $appraisal->attendance_percent = $attendance_percent;
                    $tasks_count = $this->total_tasks($appraisal->start_date, $appraisal->end_date, $appraisal->employee_id);
                    $appraisal->tasks_percent = $tasks_count;
                    printlog($tasks_count);
                
                    // Now, for each filtered daily log, calculate task aggregates
                    $appraisal->employee_daily_logs->each(function ($log) {
                        $tasks = $log->tasks; // Get the related tasks
                
                        // Calculate number of tasks
                        $log->tasks_count = $tasks->count();
                
                        // Calculate average of work_done (percentage)
                        $log->tasks_avg_work_done = $tasks->avg('work_done'); // Convert decimal to percentage
                
                        // Calculate average of hours
                        $log->tasks_avg_hours = $tasks->sum('hours');
                    });
                });
                // dd($appraisals);

            return Datatables::of($appraisals)
                ->escapeColumns(['id'])
                ->addIndexColumn() 
                ->addColumn('employee', function ($model) {

                    $employee = $model->employee;
                    return $employee->first_name . ' ' . $employee->last_name;
                })

                ->addColumn('supervisor', function ($model) {

                    $supervisor = $model->supervisor;
                    return $supervisor->first_name . ' ' . $supervisor->last_name;
                })
                ->addColumn('tasks_count', function ($model) {
                    // return $model->employee_daily_logs ? $model->employee_daily_logs->count() : 0;
                    return number_format($model->tasks_percent, 2);
                })
                ->addColumn('tasks_avg_work_done', function ($model) {
                    $totalWorkDone = $model->employee_daily_logs ? $model->employee_daily_logs->sum('tasks_avg_work_done') : 0;
                    $totalLogs = $model->employee_daily_logs->count();
                    printlog($totalWorkDone, $totalLogs);
                    
                    return $totalLogs > 0 ? round($totalWorkDone / $totalLogs, 2) . '%' : 'N/A';
                })
                ->addColumn('tasks_avg_hours', function ($model) {
                    $totalHours = $model->employee_daily_logs ? $model->employee_daily_logs->sum('tasks_avg_hours') : 0;
                    $totalLogs = $model->employee_daily_logs->count();
                    
                    // return $totalLogs > 0 ? round($totalHours / $totalLogs, 2) : 'N/A';
                    $hrs_percent = $totalLogs > 0 ? (100 / 7.4) * round($totalHours / $totalLogs, 2) : 0;
                    return number_format($hrs_percent,2);
                })

                ->editColumn('employment_date', function ($model) {

                    return (new DateTime($model->employment_date))->format('jS F, Y');
                })

                ->addColumn('employment_type', function ($model) {

                    return $model->employee->employment_type;
                })

                ->editColumn('start_date', function ($model) {

                    return (new DateTime($model->start_date))->format('jS F, Y');
                })

                ->editColumn('end_date', function ($model) {

                    return (new DateTime($model->end_date))->format('jS F, Y');
                })

                ->editColumn('score', function ($model) use ($ratings) {
                    $total = 0;
                    if($model->job_knowledge){

                        $total = ($model->job_knowledge + $model->quality_of_work + $model->communication + $model->attendance);
                    }


                    // return bcdiv($total, 4, 2);
                    $score_percent = (100/4) * bcdiv($total, 4, 2);
                    return $score_percent;
                })

                ->editColumn('job_knowledge', function ($model) use ($ratings) {

                    return @$ratings[$model->job_knowledge];
                })

                ->editColumn('quality_of_work', function ($model) use ($ratings) {

                    return @$ratings[$model->quality_of_work];
                })

                ->editColumn('communication', function ($model) use ($ratings) {

                    return @$ratings[$model->communication];
                })

                ->editColumn('attendance', function ($model) use ($ratings) {

                    return $ratings[$model->attendance];
                })
                ->addColumn('absent', function ($model) {

                    return $model->absent_days;
                })
                ->addColumn('attendance_percent', function ($model) {

                    return number_format($model->attendance_percent,2);
                })
                ->addColumn('total_percentage_avg', function ($model) {
                    //score
                    $total = ($model->job_knowledge + $model->quality_of_work + $model->communication + $model->attendance);
                    $score_percent = (100/4) * bcdiv($total, 4, 2);
                    //performance
                    $totalWorkDone = $model->employee_daily_logs ? $model->employee_daily_logs->sum('tasks_avg_work_done') : 0;
                    $totalLogs = $model->employee_daily_logs->count();
                    $company = Company::find(auth()->user()->ins);
                    if($company->performance_percent > 0){
                        $performance_percent = $totalLogs > 0 ? round($totalWorkDone / $totalLogs, 2) : 0;
                        // $performance_percent = round($totalWorkDone / $totalLogs, 2) > $company->performance_percent ? 100 : round($totalWorkDone / $totalLogs, 2);
                    }
                    $performance_percent = $totalLogs > 0 ? round($totalWorkDone / $totalLogs, 2) : 0;
                    // $performance_percent = round($totalWorkDone / $totalLogs, 2) > 80 ? 100 : round($totalWorkDone / $totalLogs, 2);
                    //task_percent

                    $task_percent = $model->tasks_percent;
                    //hours percentage
                    $totalHours = $model->employee_daily_logs ? $model->employee_daily_logs->sum('tasks_avg_hours') : 0;
                    $hrs_percent = $totalLogs > 0 ? (100 / 7.4) * round($totalHours / $totalLogs, 2) : 0;
                    //Attendance
                    $attendance_percent = $model->attendance_percent;
                    $total_percent = $score_percent + $performance_percent + $task_percent + $hrs_percent + $attendance_percent;
                    $percent = $total_percent/5;

                    return number_format($percent,2);
                })

                ->addColumn('action', function ($model) {

                    $routeEdit = route('biller.employee_appraisals.edit', $model->id);
                    $routeShow = route('biller.employee_appraisals.show', $model->id);
                    $routeDelete = route('biller.employee_appraisals.destroy', $model->id);


                    $editButton = '<a href="'.$routeEdit.'" class="btn btn-secondary round mr-1">Edit</a>';
                    $showButton = '<a href="'.$routeShow.'" class="btn btn-twitter round mr-1">View</a>';
                    $deleteButton = '<a href="' .$routeDelete . '" 
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

                    return $showButton .
                            ((access()->allow('edit-employee-appraisal')) && Auth::user()->id === $model->supervisor_id ? $editButton : '') .
                            (access()->allow('delete-employee-appraisal') ? $deleteButton : '');

                })
                ->rawColumns(['action'])
                ->make(true);
        }


        return view('focus.employeeAppraisal.index', compact('employees','appraisal_types'));
    }

    public function calculate_lateness($appraisal){
        $hrm = $appraisal->employee ? $appraisal->employee->meta : '';
        // dd($hrm->workshift);
        $hrm = $appraisal->employee ? $appraisal->employee->meta : '';
        // dd($hrm->workshift);
        if($appraisal->workshift){
            $workshifts = $appraisal->workshift->item()->where('is_checked', 0)->get()->keyBy('weekday');
        }else{
            $workshifts = $hrm->workshift->item()->where('is_checked', 0)->get()->keyBy('weekday');
        }

        // Specify the date range
        $startDate = $appraisal->start_date;
        $endDate = $appraisal->end_date;

        // Fetch attendance records with lateness within the date range
        $attendanceRecords = Attendance::where('employee_id', $appraisal->employee_id)
            ->where('status', 'late')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $totalLatenessHours = $attendanceRecords->sum(function ($record) use ($workshifts) {
            $attendanceDate = Carbon::parse($record->date);
            $weekday = $attendanceDate->format('l'); // ISO-8601: 1 for Monday, 7 for Sunday
            
            // Get the workshift for this weekday
            $shift = $workshifts->get($weekday);
            // dd($weekday,$workshifts, $shift);

            if ($shift) {
                // Define shift start and end times
                $shiftStart = Carbon::createFromTimeString($shift->clock_in); 
                $shiftEnd = Carbon::createFromTimeString($shift->clock_out); 

                // Parse attendance times (assuming the fields exist)
                $arrivalTime = Carbon::parse($record->clock_in);
                $departureTime = Carbon::parse($record->clock_out);

                // Calculate lateness
                $lateArrival = $arrivalTime->greaterThan($shiftStart) ? $arrivalTime->diffInMinutes($shiftStart) / 60 : 0;
                $earlyDeparture = $departureTime->lessThan($shiftEnd) ? $shiftEnd->diffInMinutes($departureTime) / 60 : 0;
                return $lateArrival + $earlyDeparture;
            }

            return 0; // If no shift found for that day, assume no lateness
        });

        // Output the total lateness in hours
        $totalLatenessHours = round($totalLatenessHours, 2);
        return $totalLatenessHours;
    }

    // Show the form for creating a new resource.
    public function create()
    {
        if (!access()->allow('create-employee-appraisal')) return response("", 403);

        $employees = Hrm::where('id', '!=', Auth::user()->id)->select('id', 'first_name', 'last_name')->get();
        $appraisal_types = AppraisalType::all();

        return view('focus.employeeAppraisal.create', compact('employees','appraisal_types'));
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {

        if (!access()->allow('create-employee-appraisal')) return response("", 403);

        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'supervisor_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            // 'job_knowledge' => 'required|integer|min:1|max:5',
            // 'quality_of_work' => 'required|integer|min:1|max:5',
            // 'communication' => 'required|integer|min:1|max:5',
            // 'attendance' => 'required|integer|min:1|max:5',
            'supervisor_comments' => 'nullable|string',
        ]);
        $data = $request->all();
        $data['job_knowledge'] = 4;
        $data['quality_of_work'] = 4;
        $data['communication'] = 4;
        $data['attendance'] = 4;
        // dd($request->all(), $data);

        EmployeeAppraisal::create($data);

        return redirect()->route('biller.employee_appraisals.index')
            ->with('success', 'Appraisal created successfully.');
    }

    // Display the specified resource.
    public function show($id)
    {
        if (!access()->allow('manage-employee-appraisal')) return response("", 403);

        $appraisal = EmployeeAppraisal::findOrFail($id);
        $employees = Hrm::select('id', 'first_name', 'last_name')->get();
        $ratings = [
            '1' => '1 - Poor',
            '2' => '2 - Below Average',
            '3' => '3 - Average',
            '4' => '4 - Good',
            '5' => '5 - Excellent'
        ];

        return view('focus.employeeAppraisal.show', compact('appraisal', 'employees', 'ratings'));
    }

    // Show the form for editing the specified resource.
    public function edit($id)
    {
        if (!access()->allow('edit-employee-appraisal')) return response("", 403);

        $appraisal = EmployeeAppraisal::findOrFail($id);
        $employees = Hrm::where('id', '!=', Auth::user()->id)->select('id', 'first_name', 'last_name')->get();
        $appraisal_types = AppraisalType::all();

        return view('focus.employeeAppraisal.edit', compact('appraisal', 'employees','appraisal_types'));
    }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {

        if (!access()->allow('edit-employee-appraisal')) return response("", 403);

        $request->validate([
            'supervisor_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            // 'job_knowledge' => 'required|integer|min:1|max:5',
            // 'quality_of_work' => 'required|integer|min:1|max:5',
            // 'communication' => 'required|integer|min:1|max:5',
            // 'attendance' => 'required|integer|min:1|max:5',
            'supervisor_comments' => 'nullable|string',
        ]);

        $appraisal = EmployeeAppraisal::findOrFail($id);
        $appraisal->update($request->all());

        return redirect()->route('biller.employee_appraisals.index')
            ->with('success', 'Appraisal updated successfully.');
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        if (!access()->allow('delete-employee-appraisal')) return response("", 403);

        $appraisal = EmployeeAppraisal::findOrFail($id);
        $appraisal->delete();

        return redirect()->route('biller.employee_appraisals.index')
            ->with('success', 'Appraisal deleted successfully.');
    }

    public function calculateWorkHours($userId, $startDate, $endDate) {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
    
        // Retrieve holidays within the specified date range
        $holidays = DB::table('leave_holidays')
                ->whereBetween('date', [$start, $end])
                ->pluck('date')
                ->toArray();
    
        $totalHours = 0;
        $adjust_holidays = $this->adjustHolidays($holidays);
        // dd($adjust_holidays);
    
        // Iterate through each day within the given date range
        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            // Skip Sundays and holidays
            if ($date->isSunday() || in_array($date->toDateString(), $adjust_holidays)) {
                continue;
            }
    
            // Check if there's an attendance record for the user on this date
            $attendance = Attendance::where('employee_id', $userId)
                                    ->whereDate('date', $date->toDateString())
                                    ->first();
            
    
            if ($attendance && $attendance->workshift_id) {
                // Get workshift details
                $workshift = WorkshiftItems::where('workshift_id', $attendance->workshift_id)->where('is_checked',0)->get()->keyBy('weekday');
                $attendanceDate = Carbon::parse($date->toDateString());
                $weekday = $attendanceDate->format('l'); // ISO-8601: 1 for Monday, 7 for Sunday
                $shift = $workshift->get($weekday);

                if ($shift) {
                    $clockIn = Carbon::parse($shift->clock_in);
                    $clockOut = Carbon::parse($shift->clock_out);
                    $dailyHours = $clockOut->diffInHours($clockIn);
                    $less_break = $dailyHours - $shift->hours;
                    
                    // Accumulate hours
                    $totalHours += $less_break;
                }
            }else{
                $employee = Hrm::find($userId);
                // dd($employee);
                $workshift = $employee->meta ? $employee->meta->workshift : '';
                // dd($workshift);
                if($workshift){
                    $workshift_items = $workshift->item()->where('is_checked', 0)->get()->keyBy('weekday');
                    $attendanceDate = Carbon::parse($date->toDateString());
                    $weekday = $attendanceDate->format('l'); // ISO-8601: 1 for Monday, 7 for Sunday
                    // dd($weekday);
                    
                    // Get the workshift for this weekday
                    $shift = $workshift_items->get($weekday);
                    // dd($workshift_items);v
                    if($shift){
                        $clockIn = Carbon::parse($shift->clock_in);
                        $clockOut = Carbon::parse($shift->clock_out);
                        $dailyHours = $clockOut->diffInHours($clockIn);
                        $less_break = $dailyHours - $shift->hours;
                        
                        // Accumulate hours
                        $totalHours += $less_break;
                    }
                }

            }
        }
    
        return $totalHours;
    }
    public function total_absent_days($appraisal)
    {
        $employeeMeta = $appraisal->employee ? $appraisal->employee->meta : null;

        // Determine workshift items based on appraisal or employee meta
        $workshifts = $appraisal->workshift 
            ? $appraisal->workshift->item()->where('is_checked', 0)->get()->keyBy('weekday')
            : ($employeeMeta ? $employeeMeta->workshift->item()->where('is_checked', 0)->get()->keyBy('weekday') : collect());

        // Define the appraisal date range
        $startDate = $appraisal->start_date;
        $endDate = $appraisal->end_date;

        // Fetch attendance records marked as 'absent' within the date range
        $attendanceRecords = Attendance::where('employee_id', $appraisal->employee_id)
            ->where('status', 'absent')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Calculate total absent hours based on workshift schedules
        $totalAbsentHours = $attendanceRecords->sum(function ($record) use ($workshifts) {
            $attendanceDate = Carbon::parse($record->date);
            $weekday = $attendanceDate->format('l'); // e.g., "Monday", "Tuesday"

            // Retrieve the workshift for this specific weekday
            $shift = $workshifts->get($weekday);

            if ($shift) {
                // Calculate daily working hours
                $clockIn = Carbon::parse($shift->clock_in);
                $clockOut = Carbon::parse($shift->clock_out);
                return $clockOut->diffInHours($clockIn);
            }

            return 0; // If no shift found for that day, no absent hours
        });

        // Round the total hours to two decimal places
        return round($totalAbsentHours, 2);
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

    public function total_tasks($monthStartDate, $monthEndDate, $employeeId)
    {
        $holidays = DB::table('leave_holidays')->pluck('date')->toArray();
        $holiday = $this->adjustHolidays($holidays);
        $period = CarbonPeriod::create($monthStartDate, $monthEndDate);

        // Filter out Sundays and holidays
        $validDates = collect($period)->filter(function ($date) use ($holiday) {
            return !$date->isSunday() && !in_array($date->toDateString(), $holiday);
        })->values();

        // Return the result as an array of dates (strings)
        $allDates = $validDates->map(fn($date) => $date->toDateString())->toArray();
        $allocation = EdlSubcategoryAllocation::where('employee', $employeeId)->first();
        $allocatedTaskIds = $allocation ? json_decode($allocation->allocations, true) : [];
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

        $task_fraction = ($totalExpectedCount != 0) ?  ($totalTasksDone/$totalExpectedCount)*100 : 0;

        return $task_fraction;

    }

    public function performance_evaluation(Request $request, $employee_appraisal_id)
    {
        // dd($request->all(), $employee_appraisal);
        $data = $request->except(['_token']);
        $employee_appraisal = EmployeeAppraisal::find($employee_appraisal_id);
        try {
            DB::beginTransaction();
            $employee_appraisal->update($data);
            if ($employee_appraisal){
                DB::commit();
            }
            
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return errorHandler('Error Updating Performance Evaluation', $th);
        }
        return back()->with('flash_success','Performance Evaluation Updated Successfully!!');
    }
}