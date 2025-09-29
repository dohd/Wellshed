<?php

namespace App\Console\Commands;

use App\Models\attendance\Attendance;
use App\Models\Company\Company;
use App\Models\hrm\Hrm;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateAttendanceRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:record';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record attendance records daily';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try{
            $input = [
                'month' => Carbon::now()->format('m'),
                'day' => Carbon::now()->format('d'),
                'clock_in' => [],
                'clock_out' => [],
                'status' => [],
                'status_note' => [],
                'employee_id' => []
            ];
    
            // Fetch other necessary input data here if needed
    
            // Call the update function
            $companies = Company::where('status','active')->get();
            foreach ($companies as $company) {
                # code...
                $employees = Hrm::withoutGlobalScopes(['ins'])->where('ins', $company->id)->whereNull('supplier_id')->whereNull('customer_id')->get();
                foreach ($employees as $employee) {
                    # code...
                    $input['clock_in'][] = ''; // Add clock_in for each employee
                    $input['clock_out'][] = ''; // Add clock_out for each employee
                    $input['status'][] = ''; // Add status for each employee
                    $input['status_note'][] = ''; // Add status_note for each employee
                    $input['employee_id'][] = $employee->id; // Add employee_id for each employee
                }
                // dd($input['employee_id']);
                $this->updateAttendances($input, $company->id);
                
            }
        }
        catch(\Throwable $th){
            $this->error(now() .' '. $th->getMessage() . ' at ' . $th->getFile() . ':' . $th->getLine());
        }

        $this->info('Attendance records updated successfully.');
    }

    public function updateAttendances($input, $ins) {
        // dd($input);
        // Extract required fields from the input
        $dataItems = Arr::only($input, ['clock_in', 'clock_out', 'status', 'status_note', 'employee_id']);
        $date = date_for_database(implode('-', [date('Y'), $input['month'], $input['day']]));
        
        // Fetch workshift_ids for all employees in the data items
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
        }, modify_array($dataItems));

        $employeeIds = array_map(function ($v) { return $v['employee_id']; }, $data_items);
        // $employeeIds = array_map(function ($v) { return $v['employee_id']; }, $dataItems);
        // dd($dataItems, $employeeIds);
        $workshifts = DB::table('hrm_metas')
            ->join('users', 'users.id', '=', 'hrm_metas.user_id')
            ->whereIn('users.id', $employeeIds)
            ->pluck('hrm_metas.workshift_id', 'users.id');

        // Fetch default clock_in and clock_out times from workshiftItems based on workshift_id and weekday
        $weekday = date('l', strtotime($date)); // Get the weekday (e.g., Monday)
        $defaultTimes = DB::table('workshift_items')
            ->whereIn('workshift_id', $workshifts)
            ->where('weekday', $weekday)
            ->get(['workshift_id', 'clock_in', 'clock_out'])
            ->keyBy('workshift_id');
        // dd($weekday, $defaultTimes, $workshifts, $employeeIds);
        // dd($dataItems);

        // Modify each data item to include calculated hours, date, workshift_id, and default shift times if needed
        $dataItems = array_map(function ($item) use ($date, $workshifts, $defaultTimes) {
            $workshift_id = $workshifts[$item['employee_id']] ?? null;
            
            // Set default clock_in and clock_out if not provided
            $clock_in = empty($item['clock_in']) && isset($defaultTimes[$workshift_id]) 
                        ? $defaultTimes[$workshift_id]->clock_in 
                        : $item['clock_in']; // Use the provided clock_in if it's not empty

            $clock_out = empty($item['clock_out']) && isset($defaultTimes[$workshift_id]) 
                        ? $defaultTimes[$workshift_id]->clock_out 
                        : $item['clock_out']; // Use the provided clock_out if it's not empty
            $status = 'present';
            $status_note = '';
            // dd($defaultTimes, $defaultTimes[$workshift_id]->clock_in);

            // Calculate hours worked
            $hrs = '';
            if ($clock_in && $clock_out) {
                $c1 = new DateTime($clock_in);
                $c2 = new DateTime($clock_out);
                $diff = $c2->diff($c1);
                
                // Calculate total hours as a decimal
                $totalHours = $diff->h + ($diff->i / 60);
                $hrs = number_format($totalHours, 2); // Format to two decimal places
            }
            // dd($clock_in, $item['clock_in']);
            return array_merge($item, compact('date', 'workshift_id', 'clock_in', 'clock_out','status','status_note', 'hrs'));
        }, modify_array($dataItems));
        // dd($dataItems);

        // Fetch existing attendances for the specified month and non-overtime records
        $attendances = DB::table('attendances')->whereMonth('date', $input['month'])
            ->whereIn('employee_id', $employeeIds)
            ->where('is_overtime', 0)
            ->get()
            ->keyBy(fn($attendance) => $attendance->employee_id . '-' . (int)(new DateTime($attendance->date))->format('d'));
        // dd($attendances);

        $updatedEmployeeIds = [];

        // Update existing attendances and collect IDs of updated employees
        foreach ($dataItems as &$item) {
            $key = $item['employee_id'] . '-' . $input['day'];
            if (isset($attendances[$key])) {
                $attendances[$key]->update($item);
                $updatedEmployeeIds[] = $item['employee_id'];
            }
        }

        // Filter out data items for employees that have already been updated
        $dataItems = array_filter($dataItems, function ($item) use ($updatedEmployeeIds) {
            return !in_array($item['employee_id'], $updatedEmployeeIds);
        });
        // dd($dataItems);

        // Insert remaining data items as new attendance records, if needed
        if (!empty($dataItems)) {
            $insertValues = [];
            foreach ($dataItems as $i => $item) {
                // Check if the combination of employee_id and date already exists in $insertValues
                $existing = array_filter($insertValues, function ($val) use ($item) {
                    return $val['employee_id'] === $item['employee_id'] && $val['date'] === $item['date'];
                });
        
                // Only add if not already in the insert array
                if (empty($existing)) {
                    $insertValues[] = [
                        'employee_id' => $item['employee_id'],
                        'date' => $item['date'],
                        'clock_in' => $item['clock_in'],
                        'clock_out' => $item['clock_out'],
                        'status' => $item['status'],
                        'status_note' => $item['status_note'],
                        'workshift_id' => $item['workshift_id'],
                        'hrs' => $item['hrs'],
                        'user_id' => $ins,
                        'ins' => $ins,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        
            // dd($insertValues);
        
            // Use DB::table()->insert() for batch insert
            DB::table('attendances')->insert($insertValues);
        }
        
    }
}
