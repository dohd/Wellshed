<?php

namespace App\Console;

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
        Commands\SendScheduledSms::class,
        Commands\SendScheduleEmail::class,
        Commands\SubscriptionNotification::class,
        Commands\SendBirthdaysNotify::class,
        Commands\CustomerBirthdayNotify::class,
        Commands\CreateDeliverySchedule::class,
        Commands\UpdateSmsDeliveryStatus::class,
        Commands\NotifySubscriptionExpiry::class,
        Commands\CheckSubscription::class
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
        $schedule->command('create:delivery_schedule')->everyMinute();
        $schedule->command('sms:update-status')->everyMinute();
        $schedule->command('subscriptions:notify-expiry')->everyMinute();
        // $schedule->command('send:monthly-customer-statements')->monthly();
        $schedule->command('subscription:check')->daily();

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
