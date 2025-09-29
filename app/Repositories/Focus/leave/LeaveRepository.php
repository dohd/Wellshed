<?php

namespace App\Repositories\Focus\leave;

use App\Exceptions\GeneralException;
use App\Jobs\VerifyNotifyUsers;
use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use App\Models\hrm\Hrm;
use App\Models\leave\Leave;
use App\Models\leave_category\LeaveCategory;
use App\Models\send_sms\SendSms;
use App\Repositories\BaseRepository;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Str;

class LeaveRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Leave::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        if (!access()->allow('manage-leave'))
            $q->where('employee_id', auth()->user()->id);
            
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return Leave $leave
     */
    public function create(array $input)
    {
        // dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'start_date') $input[$key] = date_for_database($val);
            if (in_array($key, ['qty', 'viable_qty'])) $input[$key] = numberClean($val);
        }
        $input['assist_employee_id'] = implode(',', $input['assist_employee_id']);
        $input['approver_ids'] = implode(',', $input['approver_ids']);

        $leave_actegory = LeaveCategory::find($input['leave_category_id']);
        if($leave_actegory->title == 'Annual Leave'){
            $holidays = DB::table('leave_holidays')->pluck('date')->toArray();
            $end_date = $this->calculateEndDate($input['start_date'], $input['qty'], $holidays);
        }else{
            $start_date = Carbon::parse($input['start_date']); // Assuming $start_date is a string

            // Add 2 days to the start date
            $endDate = $start_date->addDays($input['qty'] - 1);
            $end_date = $endDate->format('Y-m-d');
            // dd($end_date->format('Y-m-d'));
        }
        
        // Format the end date to Y-m-d
        $input['end_date'] = $end_date;
        
        $result = Leave::create($input);
        if ($result) {
            $this->notify_approvers($result);
            return $result;
        }
            
        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Leave $leave
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(Leave $leave, array $input)
    {
        // dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'start_date') $input[$key] = date_for_database($val);
            if (in_array($key, ['qty', 'viable_qty'])) $input[$key] = numberClean($val);
        }
        $input['assist_employee_id'] = implode(',', $input['assist_employee_id']);
        $input['approver_ids'] = implode(',', $input['approver_ids']);
        if(isset($input['start_date'])){
            $leave_actegory = LeaveCategory::find($input['leave_category_id']);
            if($leave_actegory->title == 'Annual Leave'){
                $holidays = DB::table('leave_holidays')->pluck('date')->toArray();
                $end_date = $this->calculateEndDate($input['start_date'], $input['qty'], $holidays);
            }else{
                $start_date = Carbon::parse($input['start_date']); // Assuming $start_date is a string
    
                // Add 2 days to the start date
                $endDate = $start_date->addDays($input['qty'] - 1);
                $end_date = $endDate->format('Y-m-d');
                // dd($end_date->format('Y-m-d'));
            }
            
            // Format the end date to Y-m-d
            $input['end_date'] = $end_date;
        }
        

        if ($leave->update($input)) return $leave;

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Leave $leave
     * @throws GeneralException
     * @return bool
     */
    public function delete(Leave $leave)
    {
        if ($leave->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.delete_error'));
    }

    public function adjustHolidays(array $holidays): array
    {
        $adjustedHolidays = [];
        foreach ($holidays as $holiday) {
            $holidayDate = Carbon::createFromFormat('Y-m-d', $holiday);
    
            // If the holiday falls on a Sunday, move it to Monday
            if ($holidayDate->isSunday()) {
                $holidayDate->addDay();
            }
    
            $adjustedHolidays[] = $holidayDate->format('Y-m-d');
        }
        return $adjustedHolidays;
    }
    
    public function calculateEndDate(string $startDate, int $leaveDays, array $holidays): string
    {
        // Adjust holidays to account for Sundays
        $holidays = $this->adjustHolidays($holidays);
    
        $currentDate = Carbon::createFromFormat('Y-m-d', $startDate);
        $daysCounted = 0; // Track valid leave days counted
    
        // Loop until the required leave days are counted
        while ($daysCounted < $leaveDays) {
            // Check if the current day is valid (not Sunday or a holiday)
            if (!$currentDate->isSunday() && !in_array($currentDate->format('Y-m-d'), $holidays)) {
                $daysCounted++;
            }
    
            // If the required days are counted, stop
            if ($daysCounted === $leaveDays) {
                break;
            }
    
            // Move to the next day
            $currentDate->addDay();
        }
    
        return $currentDate->format('Y-m-d'); // Return the calculated end date
    }
    
    public function notify_approvers($leave)
    {
        $user = $leave->employee;
        $setting = RecipientSetting::where(['type' => 'leave_notification'])->first();
        if(!$user || !$setting) return;
        $username = $user->fullname;
        $approvers = explode(',',$leave->approver_ids);
        $email_template = "Dear :name,\n\nYou have a pending leave application for review from {$username}. Kindly log in to the system to approve or provide feedback on the request at your earliest convenience.\n\nIf you need any further details regarding the application, please don't hesitate to reach out.\n\nThank you for your prompt attention.\n\nBest regards,\n";
        $sms_message = "A leave application from {$username} is pending your review. Please log in to approve or provide feedback.";
        [$user_info, $message_body, $phone_numbers, $user_ids] = $this->collectUserData($approvers,$email_template, $sms_message);
        $this->sendBulkSMS($sms_message, $phone_numbers, $user_ids, $message_body);
        $this->dispatchNotifications($user_info, "Action Required: Leave Application Pending Review");
    }

    private function collectUserData($user_ids, $email_template, $sms_template)
    {
        $pattern = '/^(0[17]\d{8}|254[17]\d{8})$/';
        $user_info = $message_body = $phone_numbers = $userIds = [];

        foreach ($user_ids as $id) {
            $user = Hrm::find($id);
            $contact = optional($user->meta)->secondary_contact;
            $cleaned = preg_replace('/\D/', '', $contact);

            if ($user && $contact && preg_match($pattern, $cleaned)) {
                $phone = preg_match('/^01\d{8}$/', $cleaned) ? '254' . substr($cleaned, 1) : $cleaned;
                $message = str_replace(":name", $user->fullname, $email_template);

                $user_info[] = [
                    'user' => [
                        'email' => $user->personal_email,
                        'id' => $user->id,
                        'phone' => $phone,
                    ],
                    'phone' => $phone,
                    'message' => $message,
                ];

                $message_body[] = ['phone' => $phone, 'message' => $sms_template];
                $phone_numbers[] = $phone;
                $userIds[] = $user->id;
            }
        }

        return [$user_info, $message_body, $phone_numbers, $userIds];
    }
    private function sendBulkSMS($template, $phones, $userIds, $message_body)
    {
        if (empty($phones)) return;

        $charCount = strlen($template);
        $cost_per_160 = 0.6;
        $total_cost = $cost_per_160 * ceil($charCount / 160) * count($userIds);

        $sms = SendSms::create([
            'subject' => $template,
            'phone_numbers' => implode(',', $phones),
            'user_type' => 'employee',
            'delivery_type' => 'now',
            'message_type' => 'single',
            'sent_to_ids' => implode(',', $userIds),
            'characters' => $charCount,
            'cost' => $cost_per_160,
            'user_count' => count($userIds),
            'total_cost' => $total_cost,
        ]);

        (new RosesmsRepository(auth()->user()->ins))->bulk_personalised_sms($message_body, $sms);
    }
     private function dispatchNotifications($user_info, $subject)
    {
        foreach ($user_info as $info) {
            if (!empty($info['user'])) {
                VerifyNotifyUsers::dispatch(auth()->user()->ins, $info['user'], $info['message'], $subject);
            }
        }
    }
}
