<?php

namespace App\Console\Commands;

use App\Models\subscription\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check expired subscriptions and suspend clients';

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
        $today = Carbon::today();
        $subscriptions = Subscription::where('status', 'active')->where('end_date', '<', $today)->get();
        foreach ($subscriptions as $sub) {
            $sub->status = 'expired';
            $sub->save();

            // Optionally notify client
            // $sub->customer->notify(new \App\Notifications\SubscriptionExpired($sub)); 
        }

        $this->info('Checked and updated subscriptions');
    }
}
