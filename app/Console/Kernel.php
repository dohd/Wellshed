<?php

namespace App\Console;

use App\Models\documentManager\DocumentManager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;


class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\DispatchJob::class,
        Commands\CreatePMEInvoice::class,
        Commands\SendMonthlyCustomerStatements::class,
        Commands\SendScheduledSms::class,
        Commands\SendScheduleEmail::class,
        Commands\SaveCallback::class,
        Commands\SendDailyBusinessMetricsEmail::class,
        Commands\SendSmsEmailNotification::class,
        Commands\UpdateAttendanceRecords::class,
        Commands\SendEmployeeReport::class,
        Commands\SubscriptionNotification::class,
        Commands\SendBirthdaysNotify::class,
        Commands\StakeholderAccessManager::class,
        Commands\SendKPIReport::class,
        Commands\SendMilestoneExpenseNotifications::class,
        Commands\TicketNotify::class,
        Commands\DocumentTrackerNotification::class,
        Commands\NotifyCustomerUnapprovedQuote::class,
        Commands\TenderNotification::class,
        Commands\TenderFollowUp::class,
        Commands\ProjectCompletionNotification::class,
        Commands\DLPNotification::class,
        Commands\CustomerBirthdayNotify::class,
        Commands\UpdateLeaveAttendance::class,
        Commands\SendDailyJSON::class,
        Commands\GenerateDbmJson::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('pme-invoice:create')->everyDay();
        $schedule->command('send:scheduled-sms')->twiceDaily(12, 01);
        $schedule->command('send:scheduled-email')->twiceDaily(12, 01);
        $schedule->command('sms:callback_save')->everyDay();
        $schedule->command('notify:sms_and_email_notification')->everyDay();
        $schedule->command('email:send-daily-business-metrics')->dailyAt('20:00');
        $schedule->command('attendance:record')->dailyAt('0:00');
        $schedule->command('send:employee_summary_report')->weeklyOn('12:00');
        $schedule->command('send:send:my_kpi_reports')->weeklyOn('12:00');
        $schedule->command('send:subscription-notification')->dailyAt('10:00');
        $schedule->command('send:birthday_wishes')->dailyAt('08:00');
        $schedule->command('manage:stakeholder-access')->everyTenMinutes();
        $schedule->command('send:milestone_expense_notifications')->everyDay();
        $schedule->command('message:notify')->everyDay();
        $schedule->command('document:tracker_expire')->everyDay();
        $schedule->command('notify:customer_unapproved_quote')->everyDay();
        $schedule->command('tender:notify_users')->everyDay();
        $schedule->command('notify:tender_follow_ups')->everyDay();
        $schedule->command('project:completion_notification')->everyDay();
        $schedule->command('notify:dlp_reminder')->everyDay();
        $schedule->command('customer:birthday_notification')->everyDay();
        $schedule->command('attendance:update-on-leave')->twiceDaily(12, 01);
        $schedule->command('json:dbm-email')->dailyAt('21:00');
        $schedule->command('json:dbm-generate')->dailyAt('20:00');
        // $schedule->command('send:monthly-customer-statements')->monthly();

        $schedule->call(function () {
            DocumentManager::checkForRenewalReminders();
        })->twiceDaily(9, 15);

        // Queue Worker
        $schedule->command('queue:work --tries=3 --timeout=90')->everyMinute()->withoutOverlapping();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
