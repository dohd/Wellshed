<?php

namespace App\Jobs;

use App;
use App\Http\Controllers\Focus\payroll\PayrollController;
use App\Models\Company\EmailSetting;
use App\Models\payroll\Payroll;
use App\Models\payroll\PayrollItemV2;
use App\Repositories\Focus\general\RosemailerRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPayslip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $payroll_item;
    private $ins;
    private $company;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payroll_item, $ins, $company)
    {
        $this->payroll_item = $payroll_item;
        $this->ins = $ins;
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $payroll_item = PayrollItemV2::withoutGlobalScopes()->where('id', $this->payroll_item->id)
        ->with([
            'employee' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'salary' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'hrmmetas' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'payroll' => function ($query) {
                $query->withoutGlobalScopes();
            },
        ])
        ->first();
        try {
            // $controller = App::make(PayrollController::class);
            $pdfOutput = $this->generatePaySlip($payroll_item, $this->company);
            $email_settings = EmailSetting::withoutGlobalScopes()->where('ins', $this->ins)->first();
            $date = $payroll_item->payroll->payroll_month;
            $monthName = date("F Y", strtotime($date));
            $text = "Dear ".$payroll_item->employee->fullname .",
                    I hope this email finds you well.
                    Please find attached your payslip for the month of ". $monthName.". This document details your earnings, deductions, and other relevant financial information for the period.
                    Should you have any questions or need further clarification regarding the payslip, feel free to reach out to the HR department at ". $email_settings->payslip_email_to ."
                    Thank you for your hard work and dedication.";
            $text1 = "Dear ".$payroll_item->employee->fullname .", 
                I hope you are doing well. Attached is your payslip for the period starting ". $monthName.".
                It includes a breakdown of your earnings, deductions, and other relevant financial details.
                If you have any questions or require further clarification, please don't hesitate to contact the HR department.
                Thank you for your continued hard work and dedication.

                Kind regards,  
                HR Department
                Email: '. $email_settings->payslip_email_to .'";
            $email_input = [
                'text' => $text1,
                'subject' => 'Your Payslip For '.$monthName,
                'mail_to' => @$payroll_item->employee->email,
                'statement' => $pdfOutput,
                'name' => $payroll_item->employee->fullname.'_payslip'
            ];
            
            (new RosemailerRepository($this->ins))->send($email_input['text'], $email_input);
        } catch (\Throwable $th) {
            Log::error('SendCustStatementJob failed: ' . $th->getMessage());
            throw $th;
        }
    }

    public function generatePaySlip($payroll_item, $company)
    {
        $resource = $payroll_item;
        $payroll = Payroll::withoutGlobalScopes()->where('id', $payroll_item->payroll_id)->first();
        // dd($resource);
        $page = "focus.bill.send_payslip";
        $params = compact('resource', 'company', 'payroll');
        $html = view($page, $params)->render(); // Load a view file as HTML

        $mpdfConfig = array_merge(config('pdf'), [
            'format' => [105, 297], // Half-width (105mm), Full-height (297mm)
        ]);
        
        $pdf = new \Mpdf\Mpdf($mpdfConfig);
        $pdf->WriteHTML($html);;
        $pdfOutput = $pdf->Output('', 'S'); // Output as a string

        return $pdfOutput;
    }

    
}
