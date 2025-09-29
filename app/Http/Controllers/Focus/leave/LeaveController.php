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

namespace App\Http\Controllers\Focus\leave;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\User\User;
use App\Models\Company\Company;
use App\Models\hrm\Hrm;
use App\Models\leave\Leave;
use App\Models\leave\LeaveApprover;
use App\Models\leave_category\LeaveCategory;
use App\Models\send_sms\SendSms;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use App\Repositories\Focus\leave\LeaveRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LeaveController extends Controller
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
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.leave.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $leave_categories = LeaveCategory::get(['id', 'title', 'qty']); 
        
        if (access()->allow('manage-leave-application')) {
            $users = Hrm::get(['id', 'first_name', 'last_name']);
        } else {
            $users = Hrm::where('id', auth()->user()->id)->get(['id', 'first_name', 'last_name']);
        }

        return view('focus.leave.create', compact('leave_categories', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            errorHandler('Error Creating Leave'.$th->getMessage(), $th);
        }
        return new RedirectResponse(route('biller.leave.index'), ['flash_success' => 'Leave Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Leave $leave
     * @return \Illuminate\Http\Response
     */
    public function edit(Leave $leave)
    {
        $leave_categories = LeaveCategory::get(['id', 'title', 'qty']);
        $users = Hrm::get(['id', 'first_name', 'last_name']);

        return view('focus.leave.edit', compact('leave', 'leave_categories', 'users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Leave $leave
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Leave $leave)
    {
        try {
            $this->repository->update($leave, $request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Leave '.$th->getMessage(), $th);
        }

        return new RedirectResponse(route('biller.leave.index'), ['flash_success' => 'Leave Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Leave $leave
     * @return \Illuminate\Http\Response
     */
    public function destroy(Leave $leave)
    {
        try {
            $this->repository->delete($leave);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Leave', $th);
        }

        return new RedirectResponse(route('biller.leave.index'), ['flash_success' => 'Leave Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  Leave $leave
     * @return \Illuminate\Http\Response
     */
    public function show(Leave $leave)
    {
        $holidays = DB::table('leave_holidays')->pluck('date')->toArray();
        $leave->return_date = calculateReturnToWorkDate(dateFormat($leave->end_date),$holidays);
        $company = Company::find(auth()->user()->ins);
        $company_name = "From " . Str::title($company->sms_email_name) .":";
        $text = $company_name . " Dear " . @$leave->employee->fullname.' your leave has been approved ';
        return view('focus.leave.view', compact('leave','text'));
    }

    /**
     * Load leave categories with viable leave days
     */
    public function leave_categories(Request $request)
    {
        $leaves = Leave::select(DB::raw('leave_category_id, SUM(qty) as total_qty'))
            ->where('employee_id', $request->employee_id)
            ->groupBy('leave_category_id')
            ->get();

        $categories = LeaveCategory::get(['id', 'title', 'qty'])
            ->map(function ($v) use($leaves) {
                foreach ($leaves as $leave) {
                    if ($v->id == $leave->leave_category_id) 
                        $v->qty -= $leave->total_qty;
                    else break;
                }
                return $v;
            });

        return response()->json($categories);
    }

    public function approve(Request $request, Leave $leave)
    {
        $input = $request->only(['status', 'status_note','date']);
        $input['leave_id'] = $leave->id;

        try {
            DB::beginTransaction();
            // Check if the status has been updated
            if (isset($input['status'])) {
                $employeeName = @$leave->employee->fullname;
                $employeeEmail = @$leave->employee->email;
                $employeePhone = @$leave->employee->meta->primary_contact;
                $employeeId = $leave->employee->id;
                
                // Find the company
                $company = Company::find(auth()->user()->ins);
                $companyName = "From " . Str::title($company->sms_email_name) . ":";
            
                // Initialize email and SMS data
                $emailInput = [
                    'subject' => 'Leave Application Update',
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

                $no_of_approvers = count(explode(',',$leave->approver_ids));
                $no_of_approved_leaves = $leave->approvers()->where('status','approved')->distinct('approved_by')->count('approved_by');
                $diff = $no_of_approvers - $no_of_approved_leaves;
                $current_approver = $leave->approvers()->where('status','approved')->where('approved_by',auth()->id())->first();
                // Handle each status case
                $input['date'] = date_for_database($input['date']);
                if ($input['status'] == 'approved') {
                    // For approval 
                    $input['approved_by'] = auth()->user()->id;
                    if($diff == 1 && !$current_approver){
                        $emailInput['text'] = $companyName ." Dear $employeeName, your leave has been approved, from ". Carbon::parse($leave->start_date)->toFormattedDateString() . " to ". Carbon::parse($leave->end_date)->toFormattedDateString() ." Please Send Handover notes to Delegates";
                        $smsText = $companyName . " Dear $employeeName, your leave has been approved, from ". Carbon::parse($leave->start_date)->toFormattedDateString() . " to ". Carbon::parse($leave->end_date)->toFormattedDateString(). " Please Send Handover notes to Delegates";
                        $this->sms_to_delegates($leave, $employeeName);
                    }
                } elseif ($input['status'] == 'rejected') {
                    // For rejection status
                    $emailInput['text'] = $companyName ." Dear $employeeName, your leave has been rejected. Contact HR for further information.";
                    $smsText = $companyName . " Dear $employeeName, your leave has been rejected. Contact HR for further information.";
                } elseif ($input['status'] == 'review') {
                    // For under review status
                    $emailInput['text'] = $companyName ." Dear $employeeName, your leave is currently under review. You will be informed of the final decision soon.";
                    $smsText = $companyName . " Dear $employeeName, your leave is under review. We will update you soon.";
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
                    (new RosemailerRepository(auth()->user()->ins))->send($emailInput['text'], $emailInput);
                    (new RosesmsRepository(auth()->user()->ins))->textlocal($employeePhone, $smsText, $smsResult);
                }
            }
            
            // Update leave status
            $res = LeaveApprover::create($input);
            if($res) DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return errorHandler('Error updating leave status '.$th->getMessage(), $th);
        }
        
        return back()->with('flash_success', 'Leave has been successfully updated');
        
    }

    public function sms_to_delegates($leave, $employeeName){
        $delegate_ids = explode(',', $leave->assist_employee_id);
        //Delegates
        $phone_numbers = [];
        $user_ids = [];
        $pattern = '/^(07\d{8}|2547\d{8})$/';
        foreach($delegate_ids as $employee){
            $user = Hrm::find($employee);
            if($user->meta){
                $cleanedNumber = preg_replace('/\D/', '', $user->meta->primary_contact);
                if (preg_match($pattern, $cleanedNumber)) {
                    $phone_numbers[] = $cleanedNumber;
                    $user_ids[] = $user->id;
                    $user_info[] = [
                        'user_name' => $user->fullname,
                        'phone' => $cleanedNumber,
                    ];
                }
            }
            
        }
        if(!empty($user_ids)){
            $totalCharacters = 0;
            $count_users = 0;
            $messageBody = [];

            foreach ($user_info as $info) {
                $message = "Dear " . $info['user_name']. ", you have been delegated duties of " . $employeeName . " who is going on leave from " 
                    . Carbon::parse($leave->start_date)->toFormattedDateString() . "to ". Carbon::parse($leave->end_date)->toFormattedDateString().". Please ensure all tasks are covered.";
                
                // Count the characters in the message
                $messageLength = strlen($message);
                
                // Add to the total character count
                $totalCharacters += $messageLength;
                $count_users += 1;
                
                // Append the message to the messageBody array
                $messageBody[] = [
                    'phone' => $info['phone'],
                    'message' => $message,
                ];
            }
            $contacts = implode(',', $phone_numbers);
            $employee = implode(',', $user_ids);
            
            $cost_per_160 = 0.6;
            $charCount = ceil($totalCharacters/160);
            $data = [
                'subject' =>"Delegated Duties for Leave",
                'user_type' =>'employee',
                'delivery_type' => 'now',
                'message_type' => 'bulk',
                'phone_numbers' => $contacts,
                'sent_to_ids' => $employee,
                'characters' => $charCount,
                'cost' => $cost_per_160,
                'user_count' => $count_users,
                'total_cost' => $cost_per_160*$charCount*$count_users,

            ];
            $result = SendSms::create($data);
            (new RosesmsRepository(auth()->user()->ins))->bulk_personalised_sms($messageBody, $result);
        }
    }
    public function get_end_date(Request $req)
    {
        // Always parse incoming start_date in known format (d-m-Y)
        $start_date = Carbon::createFromFormat('d-m-Y', $req['start_date']);
        $leave_category = LeaveCategory::find($req->leave_category_id);

        $end_date = '';

        if ($leave_category && $leave_category->title == 'Annual Leave') {
            $holidays = DB::table('leave_holidays')->pluck('date')->toArray();
            $end_date = $this->calculateEndDate($start_date, $req->days, $holidays);
        } else {
            // Add days directly for non-annual leave
            $endDate = $start_date->copy()->addDays($req['days'] - 1);
            $end_date = $endDate->format('Y-m-d');
        }

        return response()->json($end_date);
    }

    public function calculateEndDate(Carbon $startDate, int $leaveDays, array $holidays): string
    {
        // Ensure holidays are in Y-m-d format
        $holidays = adjustHolidays($holidays);

        $currentDate = $startDate->copy(); // Do not mutate original date
        $daysCounted = 0;

        while ($daysCounted < $leaveDays) {
            if (!$currentDate->isSunday() && !in_array($currentDate->format('Y-m-d'), $holidays)) {
                $daysCounted++;
            }

            if ($daysCounted === $leaveDays) break;

            $currentDate->addDay();
        }

        return $currentDate->format('d-m-Y');
    }

    public function calculateReturnToWorkDate(string $endDate, array $holidays): string
    {
        $holidays = adjustHolidays($holidays); // Ensure Y-m-d format
        $currentDate = Carbon::createFromFormat('d-m-Y', $endDate)->addDay(); // Start with next day

        while ($currentDate->isSunday() || in_array($currentDate->format('Y-m-d'), $holidays)) {
            $currentDate->addDay();
        }

        return $currentDate->format('d-m-Y');
    }

}
