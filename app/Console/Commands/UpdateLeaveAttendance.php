<?php

namespace App\Console\Commands;

use App\Models\attendance\Attendance;
use App\Models\leave\Leave;
use DB;
use Illuminate\Console\Command;

class UpdateLeaveAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:update-on-leave';
    protected $description = 'Update attendance status to on_leave for employees who are on approved leave';

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
        $this->info('Starting attendance update for employees on leave...');

        // ✅ 1. Fetch legacy single-approver approved leaves
        $singleApproverLeaves = Leave::withoutGlobalScopes()->where('status', 'approved')->get();

        // ✅ 2. Fetch multi-approver leaves where ALL approvals are "approved"
        $multiApproverLeaves = Leave::withoutGlobalScopes()->whereHas('approvers', function ($q) {
            $q->withoutGlobalScopes()->select(DB::raw('leave_id, COUNT(*) as total, SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'))
              ->groupBy('leave_id')
              ->havingRaw('total = approved');
        })->get();

        // ✅ 3. Merge both collections and avoid duplicates
        $leaves = $singleApproverLeaves->merge($multiApproverLeaves)->unique('id');

        $updatedCount = 0;

        // ✅ 4. Update attendance within leave period
        foreach ($leaves as $leave) {
            $employeeId = $leave->employee_id;
            $startDate = $leave->start_date;
            $endDate = $leave->end_date;

            $attendances = Attendance::withoutGlobalScopes()->where('employee_id', $employeeId)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('is_overtime', 0)
                ->get();

            foreach ($attendances as $attendance) {
                if ($attendance->status !== 'on_leave') {
                    $attendance->update(['status' => 'on_leave']);
                    $updatedCount++;
                }
            }
        }

        $this->info("Attendance update complete. Updated {$updatedCount} records.");
    }
}
