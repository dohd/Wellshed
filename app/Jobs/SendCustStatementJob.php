<?php

namespace App\Jobs;

use App;
use App\Models\customer\Customer;
use App\Repositories\Focus\general\RosemailerRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\Focus\customer\CustomersController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendCustStatementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $customer;
    private $company;
    private $ins;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($customer, $company, $ins)
    {
        $this->customer = $customer;
        $this->company = $company;
        $this->ins = $ins;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    
        try {
            $controller = App::make(CustomersController::class);
            $pdfOutput = $controller->generatePdf($this->customer->id, $this->company);
            $email_input = [
                'text' => "Monthly Customer Statement from " . $this->company->name.' if for more information email Accountant '.$this->company->email,
                'subject' => 'Monthly Customer Statement',
                'mail_to' => $this->customer->email,
                'statement' => $pdfOutput,
                'name' => @$this->customer->name,
            ];
            (new RosemailerRepository($this->ins))->send($email_input['text'], $email_input);
        } catch (\Throwable $th) {
            Log::error('SendCustStatementJob failed: ' . $th->getMessage());
            throw $th;
        }
    }
}
