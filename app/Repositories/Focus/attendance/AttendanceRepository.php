<?php

namespace App\Repositories\Focus\attendance;

use App\Exceptions\GeneralException;
use App\Jobs\SendAttendanceSms;
use App\Models\attendance\Attendance;
use App\Models\Company\Company;
use App\Models\leave\Leave;
use App\Models\send_sms\SendSms;
use App\Repositories\BaseRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use DateTime;
use DB;
use Illuminate\Support\Arr;

class AttendanceRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Attendance::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('employee_id'), function ($q) {
            $q->where('employee_id', request('employee_id'));
        })->when(request('status'), function ($q) {
            $q->where('status', request('status'));
        })->when(request('date'), function ($q) {
            $date = explode('-', request('date'));
            $month = intval($date[0]);
            $year = intval($date[1]);
            $q->whereYear('date', $year)->whereMonth('date', $month);
        });

        $q->when(request('employee_status'), function($q){
            $q->whereHas('employee', function($q) {
                $q->withoutGlobalScopes(['status']);
                if(request('employee_status') == 'in_active'){
                    $q->where('status',0);
                }else if(request('employee_status') == 'active'){
                    $q->where('status',1);
                }else{
                    $q->where('status',1)->orWhere('status',0);
                }
            });
        });
        $q->when(request('employee_type'), function($q){
            $q->whereHas('employee', function($q) {
                $q->withoutGlobalScopes(['status']);
                $q->whereHas('meta', fn($q) => $q->where('employment_type', request('employee_type')));
            });
        });

        return $q->orderBy('id','desc')->take(500)->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return Attendance $attendance
     */
    public function create(array $input)
    {
        // dd($input);
        // DB::beginTransaction();
        $data_items = Arr::only($input, ['clock_in', 'clock_out', 'status','status_note', 'employee_id']);
        $date = date_for_database(implode('-', [date('Y'), $input['month'], $input['day']]));

        $data_items = array_map(function ($v) use($date) {
            $hrs = '';
            if ($v['clock_in'] && $v['clock_out']) {
                $c1 = new DateTime($v['clock_in']);
                $c2 = new DateTime($v['clock_out']);
                $hrs = $c2->diff($c1)->format('%h');    
                // if ($v['status'] != 'present') 
                //     $v['status'] = 'present';
            } 
            return array_replace($v, compact('date', 'hrs'));
        }, modify_array($data_items));

        $employee_ids = array_map(function ($v) { return $v['employee_id']; }, $data_items);
        $attendances = Attendance::whereMonth('date', $input['month'])
            ->whereIn('employee_id', $employee_ids)
            ->where('is_overtime', 0)
            ->get();

        if ($attendances->count()) {
            $updated_employee_ids = [];
            // update attendance
            foreach ($attendances as $attendance) {
                foreach ($data_items as $item) {
                    $d = (int) (new DateTime($attendance['date']))->format('d');
                    $same_day = $input['day'] == $d;
                    $same_employee = $attendance->employee_id == $item['employee_id'];
                    if ($same_employee && $same_day) {
                        $attendance->update($item);
                        $updated_employee_ids[] = $item['employee_id'];
                        break;
                    } 
                }
            }
            // exclude updated data
            $data_items = array_filter($data_items, function ($v) use($updated_employee_ids) { 
                return !in_array($v['employee_id'], $updated_employee_ids);
            });
        } 
        $absent_employee_ids = [];
        $late_employee_ids = [];
        $phone_numbers = [];
        $late_phone_numbers = [];
        $user_info = [];
        $late_user_info = [];
        $pattern = '/^(07\d{8}|2547\d{8})$/';
        // save attendances
        $company = Company::find(auth()->user()->ins);
        foreach ($data_items as $item) {
            $attend = Attendance::create($item);
            // dd($item);
            if($attend->status == 'absent'){
                $cleanedNumber = preg_replace('/\D/', '', @$attend->employee->meta->primary_contact);
                if (preg_match($pattern, $cleanedNumber)) {
                    $phone_numbers[] = $cleanedNumber;
                    $absent_employee_ids[] = $attend->employee_id;
                    $user_info[] = [
                        'name' => $attend->employee ? $attend->employee->fullname : '',
                        'phone' => $cleanedNumber,
                        'date' => date('Y-m-d'),
                    ];
                }
            }
            elseif($attend->status == 'late'){
                $cleanedNumber = preg_replace('/\D/', '', @$attend->employee->meta->primary_contact);
                if (preg_match($pattern, $cleanedNumber)) {
                    $late_phone_numbers[] = $cleanedNumber;
                    $late_employee_ids[] = $attend->employee_id;
                    $late_user_info[] = [
                        'name' => $attend->employee ? $attend->employee->fullname : '',
                        'phone' => $cleanedNumber,
                        'date' => date('Y-m-d'),
                    ];
                }
            }
        }
        if(!empty($employee_ids)){
            $totalCharacters = 0;
            $count_users = 0;
            $messageBody = [];

            foreach ($user_info as $info) {
                $message = 'From ' . $company->cname . ': ' .
                        "Dear {$info['name']}, 
                        We regret to note your absence from work today, {$info['date']}. 
                        Your contribution to the team was missed, and this will impact your wages/salary. 
                        Please be advised that continued absenteeism without proper notice may result in the termination of your employment.
                        From HRM";
                
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
            $employee = implode(',', $absent_employee_ids);
            
            $cost_per_160 = 0.6;
            $charCount = ceil($totalCharacters/160);
            $data = [
                'subject' =>"Absent",
                'user_type' =>'employee',
                'delivery_type' => 'now',
                'message_type' => 'bulk',
                'phone_numbers' => $contacts,
                'sent_to_ids' => $employee,
                'characters' => $charCount,
                'cost' => $cost_per_160,
                'user_count' => $count_users,
                'total_cost' => $cost_per_160*$charCount,

            ];
            $result = SendSms::create($data);
            (new RosesmsRepository(auth()->user()->ins))->bulk_personalised_sms($messageBody, $result);
        }
        if(!empty($late_employee_ids)){
            $total_chars = 0;
            $user_count = 0;
            $message_body = [];

            foreach ($late_user_info as $info) {
                $message = 'From ' . $company->cname . ': ' .
                        "Dear {$info['name']},
                        It has been noted that you arrived late to work/left early today, {$info['date']}.
                        Our standard working hours are Monday to Friday, 8:00 AM to 5:00 PM, and Saturday, 8:30 AM to 1:00 PM, and special approved arrangements.
                        Your tardiness/early departure impacts the team and may affect your wages/salary.
                        Continued instances of arriving late or leaving early could lead to further action, including the potential termination of your employment.
                        HRM.";
                
                // Count the characters in the message
                $message_length = strlen($message);
                
                // Add to the total character count
                $total_chars += $message_length;
                $user_count += 1;
                
                // Append the message to the message_body array
                $message_body[] = [
                    'phone' => $info['phone'],
                    'message' => $message,
                ];
            }
            $contacts = implode(',', $late_phone_numbers);
            $employee = implode(',', $late_employee_ids);
            
            $cost_per_160 = 0.6;
            $charCount = ceil($total_chars/160);
            $data = [
                'subject' =>"Late/Away",
                'user_type' =>'employee',
                'delivery_type' => 'now',
                'message_type' => 'bulk',
                'phone_numbers' => $contacts,
                'sent_to_ids' => $employee,
                'characters' => $charCount,
                'cost' => $cost_per_160,
                'user_count' => $user_count,
                'total_cost' => $cost_per_160*$charCount,

            ];
            $send_sms = SendSms::create($data);
            (new RosesmsRepository(auth()->user()->ins))->bulk_personalised_sms($message_body, $send_sms);
        }

        // update employee leave status on attendance
        $attendances = Attendance::whereMonth('date', $input['month'])
            ->whereIn('employee_id', $employee_ids)
            ->where('is_overtime', 0)
            // ->take(10)
            ->get();
        $leaves = Leave::whereIn('employee_id', $employee_ids)->where('status', 'approved')
            ->get(['id', 'employee_id', 'start_date', 'end_date']);
        foreach ($attendances as $attendance) {
            foreach ($leaves as $leave) {
                if ($leave->employee_id == $attendance->employee_id) {
                    $attendance_date = new DateTime($attendance->date);
                    $leave_start = new DateTime($leave->start_date);
                    $leave_end = new DateTime($leave->end_date);
                    if ($attendance_date >= $leave_start && $attendance_date <= $leave_end) {
                        $attendance->update(['status' => 'on_leave']);
                        break;
                    }
                }
            }
        }

        if ($data_items){
            return true;
        } 
                    
        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Attendance $attendance
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(Attendance $attendance, array $input)
    {
        foreach ($input as $key => $val) {
            if ($key == 'start_date') $input[$key] = date_for_database($val);
            if (in_array($key, ['qty', 'viable_qty'])) $input[$key] = numberClean($val);
        }

        if ($attendance->update($input)) return $attendance;

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Attendance $attendance
     * @throws GeneralException
     * @return bool
     */
    public function delete(Attendance $attendance)
    {
        if ($attendance->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.delete_error'));
    }
}
