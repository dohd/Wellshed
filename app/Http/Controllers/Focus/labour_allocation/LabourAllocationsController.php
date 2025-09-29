<?php

namespace App\Http\Controllers\Focus\labour_allocation;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\casual\CasualLabourer;
use App\Models\labour_allocation\LabourAllocation;
use App\Models\labour_allocation\LabourAllocationItem;
use App\Models\quote\Quote;
use App\Repositories\Focus\labour_allocation\LabourAllocationRepository;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\misc\Misc;
use App\Models\project\Project;
use App\Models\Company\Company;
use App\Models\holiday_list\HolidayList;
use App\Models\leave\Leave;
use App\Models\project\BudgetItem;
use App\Models\salary\Salary;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Response;

/**
 *
 */
class LabourAllocationsController extends Controller
{
    /**
     * variable to store the repository object
     * @var LabourAllocationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param LabourAllocationRepository $repository ;
     */
    public function __construct(LabourAllocationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customers = Customer::all(['id', 'company']);
        $employees = Hrm::all(['id', 'first_name', 'last_name']);
        $casuals = CasualLabourer::all(['id', 'name', 'id_number']);

        return new ViewResponse('focus.labour_allocations.index', compact('customers', 'employees', 'casuals'));
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = Customer::whereHas('quotes')->get(['id', 'company']);
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $last_tid = Project::where('ins', auth()->user()->ins)->max('tid');

        $mics = Misc::all();
        $statuses = Misc::where('section', 2)->get();
        $tags = Misc::where('section', 1)->get();

        $employees = Hrm::all();
        $project = new Project();

        $casualLabourers = CasualLabourer::where('status', 'active')->get(['name', 'id']);

        return view('focus.labour_allocations.create', compact('customers', 'accounts', 'last_tid', 'project', 'mics', 'employees', 'statuses', 'tags', 'casualLabourers'));
    }

    /**
     * 
     * */
    public function getCasuals(Request $request)
    {
        $file = $request->file('csv_file');

        // parse csv to rows
        $rows = [];
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }
        
        $casuals = collect();
        foreach ($rows as $key => $row) {
            $casual = CasualLabourer::where('id_number', $row[0])
            ->orWhere('phone_number', $row[1])
            ->first();
            if ($casual) {
                $regularHrs = array_map(fn($v) => numberClean($v), array_slice($row, 3, 7));
                $overtimeHrs = array_map(fn($v) => numberClean($v), array_slice($row, 10, 7));
                $casuals->add([
                    'id' => $casual->id,
                    'name' => $row[2],
                    'id_number' => $row[0],
                    'regular_hours'=> $regularHrs,
                    'overtime_hours' => $overtimeHrs,
                ]);
            }
        }

        return response()->json($casuals);
    }


    /**
     *
     * Provides the Labour allocation data
     * Returns number of entries and total man houra from Yesterday and the current month's total
     * @return array[]
     */
    public function getLabourAllocationData(): array
    {
        //FOR THIS MONTH
        $yesterday = (new DateTime('now'))->sub(new DateInterval('P1D'));

        $yesterdayLabourAllocations = LabourAllocation::where('date', $yesterday->format('Y-m-d'))->get();

        $ylaCount = $yesterdayLabourAllocations->count();

        $ylaTotalManHours = 0;

        foreach ($yesterdayLabourAllocations as $yla){

            $ylaTotalManHours += $yla['hrs'];
        }

        $ylaMetrics = [
            'ylaCount' => $ylaCount,
            'ylaTotalManHours' => $ylaTotalManHours
        ];

        //FOR THIS MONTH
        $thisMonthLabourAllocations = LabourAllocation::whereMonth('date', date('m'))->get();

        $tmlaCount = $thisMonthLabourAllocations->count();

        $tmlaTotalManHours = 0;
        $daysInMonth = (new DateTime('now'))->format('t');

        foreach ($thisMonthLabourAllocations as $tmla){

            $tmlaTotalManHours += $tmla['hrs'];
        }

        $tmlaMetrics = [
            'tmlaCount' => $tmlaCount,
            'tmlaTotalManHours' => $tmlaTotalManHours,
            'entriesTarget' => round($daysInMonth * 12),
            'monthHoursTarget' => round($daysInMonth * 72),
        ];


        return $labourAllocationData = [
            'yesterday' => $ylaMetrics,
            'thisMonth' => $tmlaMetrics
        ];
    }


    /**
     * @throws \Exception
     */
    public function get7DaysLabourMetrics(){

        $hoursTotals = array_fill(0, 7, 0);
        $labourDates = array_fill(0, 7, 'N/A');
        for ($i = 1; $i <= 7; $i++){

            $date = (new DateTime('now'))->sub(new DateInterval('P' . $i . 'D'))->format('Y-m-d');

            $labourAllocations = LabourAllocation::where('date', $date)->pluck('hrs');
            foreach ($labourAllocations as $alloc){
                $hoursTotals[$i-1] += $alloc;
            }

            $labourDates[$i-1] = (new DateTime('now'))->sub(new DateInterval('P' . $i . 'D'))->format('jS M');
        }

        $labourDates = array_reverse($labourDates);
        $hoursTotals = array_reverse($hoursTotals);


        $startDate = (new DateTime('now'))->sub(new DateInterval('P7D'))->format('jS F');
        $endDate = (new DateTime('now'))->sub(new DateInterval('P1D'))->format('jS F');


        $chartTitle = 'Daily Labour Hours from ' . $startDate . ' to ' . $endDate . ', ' . (new DateTime('now'))->format('Y');

        return compact('hoursTotals', 'labourDates', 'chartTitle');
    }


    /**
     * @throws Exception
     */
    public function getDailyLabourHours(string $date = 'now')
    {

        $refDate = new DateTime($date);

        $month = $refDate->format('M');

        $week = $refDate->format('W');

        $daysOfTheWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $dailyTotals = array_fill(0, 7, 0);

        $weekHoursTotals = array_combine($daysOfTheWeek, $dailyTotals);

        //SALES TOTALS
        $monthLabourAllocation = LabourAllocation::whereMonth('date', $refDate->format('m'))
            ->whereYear('date', $refDate->format('Y'))->get();

        foreach ($monthLabourAllocation as $allocation) {

            $allocationWeek = (new DateTime($allocation['date']))->format('W');

            if ($allocationWeek === $week) {

                $allocationDay = (new DateTime($allocation['date']))->format('D');

                $weekHoursTotals[$allocationDay] += $allocation['hrs'];

            }

        }

        $chartTitle = "Daily Labour Hours for Week " . $week . " of " . $refDate->format('Y');

        return [
            'chartTitle' => $chartTitle,
            'weekHoursTotals' => $weekHoursTotals,
            'daysOfTheWeek' => $daysOfTheWeek,
        ];

    }


        /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->only(['period_from', 'period_to','project_id', 'hrs', 'date', 'type', 'ref_type', 'job_card', 'note', 'is_payable', 'project_milestone','task_id']);
        $task = $request->only(['date', 'note','task_id','percent_type','percent_qty']);
        $data_items = $request->only(['employee_id']);
        $casualsHrs = $request->only('casual_labourer_id', 'overtime_hrs', 'regular_hrs', 'total_reg_hrs', 'total_ot_hrs', 'total_hrs');

        $data['user_id'] = auth()->user()->id;
        $data['ins'] = auth()->user()->ins;
        $data_items = modify_array($data_items);
        $casualLabourers = array_filter($request->casual_labourer_id ?: []);

        try {
            $labourAllocation = LabourAllocation::whereNotNull('job_card')
            ->where('job_card', $request->job_card)
            ->first();

            if ($labourAllocation) {
                return redirect()->back()->with('flash_error', 'Job Card Number is already Allocated. Please Confirm Details');
            }

            $this->repository->create(compact('data', 'data_items', 'casualLabourers', 'task', 'casualsHrs'));
        } catch (Exception $exception) {
            return errorHandler('Error Creating LabourAllocation!', $exception);
        }

        return new RedirectResponse(route('biller.labour_allocations.index'), ['flash_success' => 'Labour Allocation created successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  LabourAllocation $labour_allocation
     * @return \Illuminate\Http\Response
     */
    public function edit(LabourAllocation $labour_allocation)
    {
        //dd($labour_allocation);
        $customers = Customer::whereHas('quotes')->get(['id', 'company']);
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $last_tid = Project::where('ins', auth()->user()->ins)->max('tid');

        $mics = Misc::all();
        $statuses = Misc::where('section', 2)->get();
        $tags = Misc::where('section', 1)->get();

        $employees = Hrm::withoutGlobalScopes(['status'])
            ->get(['id', 'first_name', 'last_name', 'ins', 'status']);
        $project = new Project();

        $casualLabourers = CasualLabourer::where('status', 'active')->get(['name', 'id']);

        return view('focus.labour_allocations.edit', compact('labour_allocation', 'customers', 'accounts', 'last_tid', 'project', 'mics', 'employees', 'statuses', 'tags', 'casualLabourers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  LabourAllocation $labour_allocation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LabourAllocation $labour_allocation)
    {
        $data = $request->only(['period_from', 'period_to','project_id', 'hrs', 'date', 'type', 'ref_type', 'job_card', 'note', 'is_payable', 'project_milestone','task_id']);
        $task = $request->only(['date', 'note','task_id','percent_type','percent_qty']);
        $data_items = $request->only(['employee_id']);
        $casualsHrs = $request->only('casual_labourer_id', 'overtime_hrs', 'regular_hrs', 'total_reg_hrs', 'total_ot_hrs', 'total_hrs');
        $casualLabourers = array_filter($request->casual_labourer_id ?: []);

        $data['user_id'] = auth()->user()->id;
        $data['ins'] = auth()->user()->ins;
        $data_items = modify_array($data_items);

        try {
            $this->repository->update($labour_allocation, compact('data', 'data_items', 'task', 'casualsHrs', 'casualLabourers'));
        } catch (Exception $e) {
            return errorHandler('Error Updating LabourAllocation!', $e);
        }

        return new RedirectResponse(route('biller.labour_allocations.index'), ['flash_success' => 'Labour Allocation Updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  LabourAllocation $labour_allocation
     * @return \Illuminate\Http\Response
     */
    public function destroy(LabourAllocation $labour_allocation)
    {
        try {
            $this->repository->delete($labour_allocation);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting LabourAllocation!', $th);
        }

        return new RedirectResponse(route('biller.labour_allocations.index'), ['flash_success' => 'LabourAllocation Deleted Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  LabourAllocation $labour_allocation
     * @return \Illuminate\Http\Response
     */
    public function show(LabourAllocation $labour_allocation)
    {
        $project = $labour_allocation->project;
        $customer_branch = '';
        if ($project) {
            $customer = $project->customer ? $project->customer->name : '';
            $branch = $project->branch ? $project->branch->name : '';
            $customer_branch = $customer . ' - ' . $branch;
        }
        $employee = [];
        foreach ($labour_allocation->items as $item) {
            $employee[] = [
                'employee_name' => $item->employee ? $item->employee->first_name . ' ' . $item->employee->last_name : '',
                'id' => $item->id,
                'employee_id' => $item->employee_id,
            ];
        }

        return view('focus.labour_allocations.view', compact('labour_allocation', 'customer_branch', 'employee'));
    }


    /** 
     * Casuals Select List
     * 
     * */
    public function selectCasuals(Request $request) 
    {
        $q = $request->term;
        $casuals = CasualLabourer::where('name', 'LIKE', '%'.$q.'%')
            ->where('status', 'active')
            ->limit(6)
            ->get(['id', 'id_number', 'name']);

        return response()->json($casuals);
    }
    
    
    /** Expected Allocation Hrs */
    public function expected_hours(Request $request)
    {
        $result = ['hours' => 0];
        try {
            $project_id = request('project_id');
            $project = Project::find($project_id);
            
            $quote = Quote::find(request('quote_id'));
            if (@$quote->project) $project = $quote->project;
            if ($project) {
                $project_id = $project->id;
                $result['project_id'] = $project->id;
                $result['project_name'] = $project->name;
                $result['progress'] = $project->progress ?? 0;
                $result['project_tid'] = gen4tid('PRJ-', $project->tid);
                $result['quote_tid'] = [];
                foreach ($project->quotes as $quote) {
                    $result['quote_tid'][] = gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid);
                }
                $result['quote_tid'] = implode(',', $result['quote_tid']);
            }
            
            $total_project_hrs = BudgetItem::whereHas('budget', function($q) use($project_id) {
                $q->whereHas('quote', function($q) use($project_id) {
                    $q->whereHas('project', fn($q) => $q->where('projects.id', $project_id));
                });
            })->whereHas('product', function($q) {
                $q->whereHas('product', function($q) {
                    $q->where('stock_type', 'service');
                    // use units since service had indistinguishable data (qty column used for rate)
                    $q->whereHas('units', fn($q) => $q->where('code', 'Mnhr'));
                });
            })->sum('new_qty');

            $total_alloc_hrs = LabourAllocation::where('project_id', request('project_id'))->sum('hrs');
            $result['hours'] = $total_project_hrs - $total_alloc_hrs;
        } catch (\Throwable $th) {
            throw $th;
        }

        return response()->json($result);
    }
    
    /** Employee Labour Rate per Hour */
    public function employee_hourly_rate(Request $request)
    {
        $rate = 0;
        try {
            $salary = Salary::where('employee_id', request('employee_id'))
                ->where('pay_per_hr', '>', 0)
                ->latest()->first();
            if ($salary) $rate = +$salary->pay_per_hr;
        } catch (\Throwable $th) {
            //throw $th;
        }
        
        return response()->json(['rate' => $rate]);
    }

    
    public function attach_employee($id, $employee_id)
    {
        $labour = LabourAllocation::find($id);
        $employee = $labour->employee ? $labour->employee->first_name . ' ' . $labour->employee->last_name : '';
        $labour_items = $labour->items()->get();
        return view('focus.labour_allocations.attach_employee', compact('id', 'employee_id', 'labour', 'labour_items', 'employee'));
    }

    public function get_employee_items(Request $request)
    {
        $labour = LabourAllocation::find($request->id);
        $labour_items = $labour->items()->get();
        return DataTables::of($labour_items)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('date', function ($labour_item) {
                return dateFormat($labour_item->date);
            })
            ->addColumn('hrs', function ($labour_item) {
                return numberFormat($labour_item->hrs);
            })
            ->addColumn('type', function ($labour_item) {
                return $labour_item->type;
            })
            ->addColumn('actions', function ($labour_item) {
                return '<a href="' . route('biller.labour_allocations.show', $labour_item->id) . '" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="View"><i  class="fa fa-eye"></i></a>' . '<a href="' . route('biller.labour_allocations.edit_item', $labour_item->id) . '" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="fa fa-pencil "></i></a>' . '<a href="' . route('biller.labour_allocations.delete_item', $labour_item->id) . '" class="btn btn-danger round" data-toggle="tooltip" data-placement="top" title="Delete"><i  class="fa fa-trash"></i></a>';
            })
            ->make(true);
        return response()->json($labour_items);
    }
    
    public function store_labour_items(Request $request)
    {
        $data = $request->only(['date', 'labour_id', 'hrs', 'type', 'is_payable']);
        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        if (isset($data['is_payable'])) $data['is_payable'] = intval($data['is_payable']);
        LabourAllocationItem::create($data);
        return redirect()
            ->back()
            ->with('flash_success', 'Employee Data added!!');
    }
    public function delete_item($id)
    {
        //dd($id);
        LabourAllocationItem::find($id)->delete();
        return redirect()
            ->back()
            ->with('flash_success', 'Employee Deleted Successfully!!');
    }
    public function edit_item($id)
    {
        //dd($id);
        $labour_item = LabourAllocationItem::find($id);
        return view('focus.labour_allocations.edit_item', compact('labour_item'));
    }
    public function update_item(Request $request, $labour_item)
    {
        $data = $request->only(['date', 'labour_id', 'hrs', 'type']);
        $item = LabourAllocationItem::find($labour_item);
        $item->update($data);
        $employee_id = $item->labour->employee_id;
        // dd($employee_id);

        return new RedirectResponse(route('biller.labour_allocations.attach_employee', [$labour_item, $employee_id]), ['flash_success' => 'Labour Items Updated Successfully']);
    }
    public function delete_labour($id)
    {
        //dd($id);
        $labour = LabourAllocation::find($id);
        $labour->delete();
        $labour->items->each->delete();
        return redirect()
            ->back()
            ->with('flash_success', 'Employee Deleted Successfully!!');
    }
    public function employee_summary()
    {
        $employees = Hrm::all(['id', 'first_name', 'last_name']);
        $casuals = CasualLabourer::all(['id', 'name', 'id_number']);
        return view('focus.labour_allocations.employee_summary', compact('employees', 'casuals'));
    }

    public function employee_summary_report($user_id, $month, $year){
        // dd($user_id,$month,$year);
        $q = LabourAllocationItem::withoutGlobalScopes()->where('employee_id', $user_id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get()
                ->map(function($v){
                    $v->employee = $v->employee()->withoutGlobalScopes()->first() ? $v->employee()->withoutGlobalScopes()->first()->fullname : '';

                    // Access project without global scopes
                    $labour = $v->labour()->withoutGlobalScopes()->first();
                    $project = $labour->project()->withoutGlobalScopes()->first();
                    $v->project = $project ? gen4tid('PRJ-', $project->tid) : '';
                    $v->project_name = $project ? $project->name : '';

                    // Access labour and project within labour without global scopes
                    $project = $labour ? $labour->project()->withoutGlobalScopes()->first() : null;

                    if ($project) {
                        $customer = $project->customer()->withoutGlobalScopes()->first()->company ?? '';
                        $branch = $project->branch()->withoutGlobalScopes()->first()->name ?? '';
                        if ($branch) {
                            $customer .= " - {$branch}";
                        }
                        $v->customer = $customer;
                    }

                    $tids = [];
                    // $labour = $item->labour;
                    if($labour){
                        $proj = $project;
                        if($proj){
                            foreach ($proj->quotes()->withoutGlobalScopes()->get() as $quote) {
                                $tid = gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid);
                                $tids[] =  $tid;
                            }
                        }
                    }
                    $v->tids = implode(', ', $tids);
                    $v->jobcard = $labour ? $labour->job_card : '';

                    return $v;
                });
                // dd($q);
        $headers = [
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $employee = Hrm::withoutGlobalScopes()->find($user_id);
        $company = Company::find($employee->ins);
        $new_date = DateTime::createFromFormat('Y-m', "$year-$month");
        $formattedDate = $new_date->format('F Y');  
        $report = $this->generateWeeklyReportTable($user_id, $month, $year);
        $data = [
            'report' => $q,
            'title' => 'Employee Summary Report',
            'month_year' => $formattedDate,
            'company' => $company,
            'week_report' => $report
        ];
        $html = view('focus.report.pdf.employee_report', ['data'=>$data])->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);

        return Response::stream($pdf->Output('purchaseorder.pdf', 'I'), 200, $headers);
    }
    function getWeekEndingDaysForPreviousWeeks($month = null, $year = null) {
        $month = $month ?: now()->month;
        $year = $year ?: now()->year;
    
        $startOfMonth = Carbon::createFromDate($year, $month, 1);
        $lastDayOfMonth = $startOfMonth->copy()->endOfMonth();
        $weekEndingDays = [];
    
        // Loop through the month in chunks of 7 days
        $currentDay = $startOfMonth->copy();
        while ($currentDay->lte($lastDayOfMonth)) {
            // Calculate the week-ending day (7 days from the current day)
            $weekEnding = $currentDay->copy()->addDays(6);
    
            // If the calculated week-ending exceeds the last day of the month, adjust it
            if ($weekEnding->gt($lastDayOfMonth)) {
                $weekEnding = $lastDayOfMonth;
            }
    
            // Store the calculated week-ending day
            $weekEndingDays[] = $weekEnding->toDateString();
    
            // Move to the next week (add 7 days)
            $currentDay->addDays(7);
        }
        return $weekEndingDays;
    }
    
    
    function calculateTargetHours($startOfWeek, $endOfWeek, $user_id) {
        // Workshift rules: weekdays = 8 hours, Saturday = 4.5 hours
        $targetHours = 0;
    
        // Fetch holidays
        $holidays = HolidayList::withoutGlobalScopes()->whereBetween('date', [$startOfWeek, $endOfWeek])->pluck('date')->toArray();
    
        // Fetch leave periods
        $leavePeriods = Leave::withoutGlobalScopes()->where('employee_id', $user_id)
            ->where('status', 'approved')
            ->where(function ($query) use ($startOfWeek, $endOfWeek) {
                $query->whereBetween('start_date', [$startOfWeek, $endOfWeek])
                      ->orWhereBetween('end_date', [$startOfWeek, $endOfWeek])
                      ->orWhere(function ($subQuery) use ($startOfWeek, $endOfWeek) {
                          $subQuery->where('start_date', '<', $startOfWeek)
                                   ->where('end_date', '>', $endOfWeek);
                      });
            })
            ->get(['start_date', 'end_date']);
    
        // Generate leave days within the week
        $leaveDays = [];
        foreach ($leavePeriods as $leave) {
            $leaveStart = Carbon::parse($leave->start_date)->greaterThan($startOfWeek) ? Carbon::parse($leave->start_date) : $startOfWeek;
            $leaveEnd = Carbon::parse($leave->end_date)->lessThan($endOfWeek) ? Carbon::parse($leave->end_date) : $endOfWeek;
            
            for ($date = $leaveStart->copy(); $date->lte($leaveEnd); $date->addDay()) {
                $leaveDays[] = $date->toDateString();
            }
        }
    
        $holidays = adjustHolidays($holidays);
        $excludedDays = array_merge($holidays, $leaveDays);
    
        // Iterate from startOfWeek to endOfWeek (inclusive)
        for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
            printlog("Processing: " . $date);
            if ($endOfWeek->day === 7 && $date->day > 5 && $date->day < 8) {
                printlog("Including (7th of the month): " . $date->toDateString());
                $targetHours += $date->isSaturday() ? 4.5 : 8;
                continue; // Skip further checks for the 7th
            }
            if ($date->isSunday()) {
                printlog("Skipping (Sunday): " . $date->toDateString());
                continue;
            }
        
            if (in_array($date->toDateString(), $excludedDays)) {
                printlog("Skipping (excluded day): " . $date->toDateString());
                continue;
            }
        
            
            $targetHours += $date->isSaturday() ? 4.5 : 8;
        }
        
        // dd($targetHours,$startOfWeek, $endOfWeek);
    
        return $targetHours;
    }
    
    
    function generateWeeklyReportTable($user_id, $month, $year) {
        $weekEndingDays = $this->getWeekEndingDaysForPreviousWeeks($month, $year);
        $lastDayOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        $reportData = [];
        $startOfWeek = Carbon::createFromDate($year, $month, 1);
    
        $cumulativeActualHours = 0;
        $cumulativeTargetHours = 0;
    
        foreach ($weekEndingDays as $weekIndex => $weekEndingDay) {
            $endOfWeek = Carbon::parse($weekEndingDay);
    
            // Fetch actual hours for the week (inclusive of start and end dates)
            $data = LabourAllocationItem::withoutGlobalScopes()
                ->where('employee_id', $user_id)
                ->whereBetween('date', [$startOfWeek, $endOfWeek])
                ->get();
    
            $actualHours = $data->sum('hrs'); // Sum worked hours
    
            // Calculate target hours dynamically
            $targetHoursForWeek = $this->calculateTargetHours($startOfWeek, $endOfWeek, $user_id);
            // printlog($startOfWeek,$endOfWeek);
    
            // Update cumulative totals
            $cumulativeActualHours += $actualHours;
            $cumulativeTargetHours += $targetHoursForWeek;
    
            // Calculate cumulative fraction
            $cumulativeFraction = $cumulativeTargetHours > 0
                ? $cumulativeActualHours . '/' . $cumulativeTargetHours
                : 0;
    
            $reportData[] = [
                'week' => "Week " . ($weekIndex + 1),
                'target_hours' => $targetHoursForWeek,
                'actual_hours' => $actualHours,
                'cumulative_fraction' => $cumulativeFraction,
            ];
    
            $startOfWeek = $endOfWeek->copy()->addDay();
        }
    
        // Handle the remaining days of the month as "Week 5"
        if ($startOfWeek->lte($lastDayOfMonth)) {
            $data = LabourAllocationItem::withoutGlobalScopes()
                ->where('employee_id', $user_id)
                ->whereBetween('date', [$startOfWeek, $lastDayOfMonth])
                ->get();
    
            $actualHours = $data->sum('hrs'); // Sum worked hours
            $targetHoursForWeek = $this->calculateTargetHours($startOfWeek, $lastDayOfMonth, $user_id);
    
            $cumulativeActualHours += $actualHours;
            $cumulativeTargetHours += $targetHoursForWeek;
    
            $cumulativeFraction = $cumulativeTargetHours > 0
                ? $cumulativeActualHours . '/' . $cumulativeTargetHours
                : 0;
    
            $reportData[] = [
                'week' => "Week " . (count($weekEndingDays) + 1), // Week 5
                'target_hours' => $targetHoursForWeek,
                'actual_hours' => $actualHours,
                'cumulative_fraction' => $cumulativeFraction,
            ];
        }
    
        return $reportData;
    }
}
