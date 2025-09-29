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

namespace App\Http\Controllers\Focus\attendance;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\User\User;
use App\Models\attendance\Attendance;
use App\Models\Company\Company;
use App\Models\hrm\Hrm;
use App\Models\send_sms\SendSms;
use App\Models\workshift\Workshift;
use App\Repositories\Focus\attendance\AttendanceRepository;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use Carbon\Carbon;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    /**
     * variable to store the repository object
     * @var AttendanceRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AttendanceRepository $repository ;
     */
    public function __construct(AttendanceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = Hrm::get(['id', 'first_name', 'last_name']);
        
        return new ViewResponse('focus.attendances.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $employees = Hrm::get(['id', 'first_name', 'last_name']);
        $company = Company::find(auth()->user()->ins);

        return view('focus.attendances.create', compact('employees', 'company'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->repository->create($request->except('_token'));

        return new RedirectResponse(route('biller.attendances.index'), ['flash_success' => 'Attendance Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Attendance $attendance
     * @return \Illuminate\Http\Response
     */
    public function edit(Attendance $attendance)
    {
        $company = Company::find(auth()->user()->ins);

        return view('focus.attendances.edit', compact('employees', 'company'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Attendance $attendance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Attendance $attendance)
    {
        $this->repository->update($attendance, $request->except('_token'));

        return new RedirectResponse(route('biller.attendances.index'), ['flash_success' => 'Attendance Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Attendance $attendance
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attendance $attendance)
    {
        $this->repository->delete($attendance);

        return new RedirectResponse(route('biller.attendances.index'), ['flash_success' => 'Attendance Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  Attendance $attendance
     * @return \Illuminate\Http\Response
     */
    public function show(Attendance $attendance)
    {
        $workshifts = Workshift::all();
        return view('focus.attendances.view', compact('attendance', 'workshifts'));
    }

    /**
     * Day Attendance Count
     */
    public function day_attendance(Request $request)
    {
        $attendances = Attendance::whereMonth('date', $request->month)
            ->get(['employee_id', 'date'])->toArray();

        $day_employee_group = array_reduce($attendances, function ($init, $curr) {
            $d = (new DateTime($curr['date']))->format('j');
            $key_exists = in_array($d, array_keys($init));
            if (!$key_exists) $init[$d] = array();
            $init[$d][] = $curr['employee_id'];
            
            return $init;
        }, []);

        $day_attendance = array();
        foreach ($day_employee_group as $key => $val) {
            $day_attendance[] = array(
                'day' => $key,
                'count' => count(array_unique($val))
            );
        }

        $employee_count = User::count();

        return response()->json(compact('employee_count', 'day_attendance'));
    }

    /**
     * Attendance employees
     */
    public function employees_attendance(Request $request)
    {
        $attendances = Attendance::whereMonth('date', $request->month)
            ->whereDay('date', $request->day)
            ->with(['employee' => function ($q) {
                $q->select('id', 'first_name', 'last_name');
            }])
            ->get();

        return response()->json($attendances);
    }

    public function update_status($attendance_id, Request $request){
        // dd($attendance_id, $request->all());
        $attendance = Attendance::find($attendance_id);
        try {
            // DB::beginTransaction();
            $data = $request->only([
                'status','clock_in','clock_out','workshift_id','status_note'
            ]);
            if($request->type == 'clock_in' && $data['status'] == 'late'){
                unset($data['clock_out']);
                 //send sms for lateness
            }else if($request->type == 'clock_out' && $data['status'] == 'late'){
                unset($data['clock_in']);
            }

            if($data['status'] == 'absent'){
                //send sms for absent
            }
            $attendance->update($data);
            $this->send_sms($attendance, $request->type);
            // if($attendance){
            //     DB::commit();
            // }
            // dd($data);
        } catch (\Throwable $th) {
            //throw $th;
            // DB::rollback();
            return errorHandler('Error updating status', $th);
        }
        return back()->with('flash_success', "Status Update Success");
    }
    public function send_sms($attendance, $type)
    {
        $employeeName = @$attendance->employee->fullname;
        $employeeEmail = @$attendance->employee->email;
        $employeePhone = @$attendance->employee->meta->primary_contact;
        $employeeId = $attendance->employee->id;
        
        // Find the company
        $company = Company::find(auth()->user()->ins);
        $companyName = "From " . Str::title($company->sms_email_name) . ":";
    
        // Initialize email and SMS data
        $emailInput = [
            'subject' => 'Attendance Status Update',
            'mail_to' => $employeeEmail,
            'name' => $employeeName,
        ];
        
        $smsData = [
            'user_type' => 'employee',
            'delivery_type' => 'now',
            'message_type' => 'single',
            'phone_numbers' => $employeePhone,
            'sent_to_ids' => $employeeId,
        ];
    
        // Handle each status case
        $total_hours = $this->calculate_lateness($attendance);
        if ($attendance->status == 'late' && $type == 'clock_in' && $total_hours > 0) {
            // For approval status
            $emailInput['text'] = "Dear {$employeeName}, It has been noted that you arrived late to work today, {$attendance->date} at {$attendance->clock_in}. Our standard working hours are Monday to Friday, 8:00 AM to 5:00 PM, and Saturday, 8:30 AM to 1:00 PM, and special approved arrangements. Your tardiness/early departure impacts the team and may affect your wages/salary. Continued instances of arriving late or leaving early could lead to further action, including the potential termination of your employment. HRM.";
            $smsText = $companyName . " Dear {$employeeName}, It has been noted that you arrived late to work today, {$attendance->date} at {$attendance->clock_in}. Our standard working hours are Monday to Friday, 8:00 AM to 5:00 PM, and Saturday, 8:30 AM to 1:00 PM, and special approved arrangements. Your tardiness/early departure impacts the team and may affect your wages/salary. Continued instances of arriving late or leaving early could lead to further action, including the potential termination of your employment. HRM.";
        } elseif ($attendance['status'] == 'late' && $type == 'clock_out' && $total_hours > 0) {
            // For rejection status
            $emailInput['text'] = "Dear {$employeeName}, It has been noted that you left early today, {$attendance->date} at {$attendance->clock_out}. Our standard working hours are Monday to Friday, 8:00 AM to 5:00 PM, and Saturday, 8:30 AM to 1:00 PM, and special approved arrangements. Your tardiness/early departure impacts the team and may affect your wages/salary. Continued instances of arriving late or leaving early could lead to further action, including the potential termination of your employment. HRM.";
            $smsText = $companyName. " Dear {$employeeName}, It has been noted that you left early today, {$attendance->date} at {$attendance->clock_out}. Our standard working hours are Monday to Friday, 8:00 AM to 5:00 PM, and Saturday, 8:30 AM to 1:00 PM, and special approved arrangements. Your tardiness/early departure impacts the team and may affect your wages/salary. Continued instances of arriving late or leaving early could lead to further action, including the potential termination of your employment. HRM.";
        } elseif ($attendance['status'] == 'absent') {
            // For under review status
            $emailInput['text'] = "Dear {$employeeName}, We regret to note your absence from work today, {$attendance->date}. Your contribution to the team was missed, and this will impact your wages/salary. Please be advised that continued absenteeism without proper notice may result in the termination of your employment. From HRM";
            $smsText = $companyName ." Dear {$employeeName}, We regret to note your absence from work today, {$attendance->date}. Your contribution to the team was missed, and this will impact your wages/salary. Please be advised that continued absenteeism without proper notice may result in the termination of your employment. From HRM";
        }
    
        // Only proceed if the status is one of the above (approved, rejected, or under review)
        if (isset($smsText)) {
            // Prepare SMS data
            $smsData['subject'] = $smsText;
            $cost_per_160 = 0.6;
            $charCount = strlen($smsText);
            $blocks = ceil($charCount / 160);
            $smsData['characters'] = $charCount;
            $smsData['cost'] = $cost_per_160;
            $smsData['user_count'] = 1;
            $smsData['total_cost'] = $cost_per_160*$blocks;
    
            // Send SMS and email
            $smsResult = SendSms::create($smsData);
            // (new RosemailerRepository(auth()->user()->ins))->send($emailInput['text'], $emailInput);
            // (new RosesmsRepository(auth()->user()->ins))->textlocal($employeePhone, $smsText, $smsResult);
        }
        return;
    }

    public function calculate_lateness($attendance){
        $hrm = $attendance->employee ? $attendance->employee->meta : '';
        // dd($hrm->workshift);
        if($attendance->workshift){
            $workshifts = $attendance->workshift->item()->where('is_checked', 0)->get()->keyBy('weekday');
        }else{
            $workshifts = $hrm->workshift->item()->where('is_checked', 0)->get()->keyBy('weekday');
        }

        // Specify the date range
        $startDate = $attendance->date;
        $endDate = $attendance->date;

        // Fetch attendance records with lateness within the date range
        $attendanceRecords = Attendance::where('employee_id', $attendance->employee_id)
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
}
