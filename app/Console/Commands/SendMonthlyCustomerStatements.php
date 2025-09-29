<?php

namespace App\Console\Commands;

use App\Http\Controllers\Focus\customer\CustomersController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class SendMonthlyCustomerStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:monthly-customer-statements';
    //cd C:\LaravelApps\lvl-erp-v2 && php artisan send:monthly-customer-statements
    


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly statements to customers';

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
        $controller = App::make(CustomersController::class);
        return $controller->sendMonthlyStatements();
    }
}
