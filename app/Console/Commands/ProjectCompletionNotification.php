<?php

namespace App\Console\Commands;

use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\project\Project;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Models\tenant\Tenant;
use App\Repositories\Focus\general\RosemailerRepository;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProjectCompletionNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:completion_notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Project Completion Notification';

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
         $tenants = Tenant::where('status', 'Active')->get();
     
         foreach ($tenants as $tenant) {
             $smsServer = SmsSetting::withoutGlobalScopes()->where('ins', $tenant->id)->first();
             $setting = RecipientSetting::withoutGlobalScopes()
                 ->where('type', 'project_completion')
                 ->where('ins', $tenant->id)
                 ->first();
     
             if (!$smsServer || !$setting) {
                 continue; // Skip tenant if settings are missing
             }
     
             $this->processProjects($tenant, $today, "Reminder: Project Ends Today", $smsServer, $setting);
             
             // Fetch all projects and send reminders based on their reminder_days
             $projects = Project::withoutGlobalScopes()
                 ->where('ins', $tenant->id)
                 ->where('name', '!=', 'Completed')
                 ->get();
     
             foreach ($projects as $project) {
                 if ($project->reminder_days !== null) {
                     $reminderDate = Carbon::parse($project->end_date)->subDays($project->reminder_days);
                     if ($reminderDate->isToday()) {
                         $this->processProjects($tenant, $reminderDate, "Reminder: Project Ends in {$project->reminder_days} Days", $smsServer, $setting);
                     }
                 }
             }
         }
     }
     
     private function processProjects($tenant, $date, $emailSubject, $smsServer, $setting)
     {
         $projects = Project::withoutGlobalScopes()
             ->whereHas('misc', fn($q) => $q->withoutGlobalScopes()
                 ->whereDate('end_date', $date)
                 ->where('name', '!=', 'Completed'))
             ->where('ins', $tenant->id)
             ->get();
     
         foreach ($projects as $project) {
             $this->notifyEmployees($project, $tenant, $emailSubject, $smsServer, $setting);
         }
     }
    
    private function notifyEmployees($project, $tenant, $emailSubject, $smsServer, $setting)
    {
        $employees = $project->users()->withoutGlobalScopes()->get();
        $projectNo = gen4tid("PRJ-", $project->tid);
        $customer = $project->customer()->withoutGlobalScopes()->first();
        $customerName = $customer->company ?: $customer->name;
        $branch = $project->branch()->withoutGlobalScopes()->first();
        $branchName = $branch ? $branch->name : 'Head Office';
    
        foreach ($employees as $employee) {
            $phoneNumber = $this->formatPhoneNumber($employee->meta()->withoutGlobalScopes()->first()->primary_contact);
            if (!$phoneNumber) {
                continue; // Skip if no valid phone number
            }
    
            $date_today = Carbon::today()->format('Y-m-d');  // Ensures format is Y-m-d
            $dueDate = Carbon::parse($project->end_date)->format('d/m/Y'); // Formats for display
            
            $message = "Dear {$employee->fullname}, Your project {$projectNo} - {$project->name} for {$customerName} - {$branchName} should be " . 
                       ($project->end_date == $date_today ? "complete by today {$dueDate}" : "completed by {$dueDate}");
            
            // Send Email Notification
            if ($setting->email == 'yes') {
                $this->sendEmailNotification($employee, $message, $emailSubject);
            }
    
            // Send SMS Notification
            if ($setting->sms == 'yes' && $smsServer->active == 1) {
                $this->sendSmsNotification($tenant, $employee, $phoneNumber, $message);
            }
        }
    }
    
    private function formatPhoneNumber($number)
    {
        if (!$number) return null;
    
        $cleanedNumber = preg_replace('/\D/', '', $number);
        if (preg_match('/^(0[17]\d{8}|254[17]\d{8})$/', $cleanedNumber)) {
            return preg_match('/^01\d{8}$/', $cleanedNumber) ? '254' . substr($cleanedNumber, 1) : $cleanedNumber;
        }
        return null;
    }
    
    private function sendEmailNotification($employee, $message, $subject)
    {
        $emailInput = [
            'text' => $message,
            'subject' => $subject,
            'mail_to' => $employee->email,
        ];
    
        $emailResponse = (new RosemailerRepository($employee->ins))->send($message, $emailInput);
        $emailOutput = json_decode($emailResponse);
    
        if ($emailOutput->status === "Success") {
            SendEmail::create([
                'text_email' => $subject,
                'subject' => $subject,
                'user_emails' => $employee->email,
                'user_ids' => $employee->id,
                'ins' => $employee->ins,
                'status' => 'sent',
            ]);
        }
    }
    
    private function sendSmsNotification($tenant, $employee, $phoneNumber, $message)
    {
        $smsData = [
            'subject' => "From {$tenant->sms_email_name}: {$message}",
            'user_type' => 'employee',
            'delivery_type' => 'now',
            'message_type' => 'bulk',
            'phone_numbers' => $phoneNumber,
            'sent_to_ids' => $employee->id,
            'characters' => ceil(strlen($message) / 160),
            'cost' => 0.6,
            'user_count' => 1,
            'total_cost' => 0.6 * ceil(strlen($message) / 160),
            'user_id' => $tenant->id,
            'ins' => $tenant->id,
        ];
    
        $sendSms = new SendSms();
        $sendSms->fill($smsData);
        $sendSms->save();
    
        $this->bulkSms($phoneNumber, $message, $sendSms, $tenant->id);
    }
    
    public function bulkSms($mobile, $textMessage, $sendSms, $ins)
    {
        $smsServer = SmsSetting::withoutGlobalScopes()->where('ins', $ins)->first();
        if (!$smsServer) return;
    
        $payload = [
            'senderID' => $smsServer->sender,
            'message' => $textMessage,
            'phones' => $mobile,
        ];
    
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://api.mobilesasa.com/v1/send/bulk', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $smsServer->username,
                ],
                'json' => $payload,
            ]);
    
            $content = json_decode($response->getBody()->getContents(), true);
    
            if (isset($content['bulkId'])) {
                SmsResponse::withoutGlobalScopes()->create([
                    'send_sms_id' => $sendSms->id,
                    'message_response_id' => $content['bulkId'],
                    'status' => 1,
                    'message_type' => 'bulk',
                    'phone_number_count' => count(explode(',', $mobile)),
                    'response_code' => $content['responseCode'] ?? null,
                    'ins' => $ins,
                    'user_id' => auth()->id() ?? $ins,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("SMS Sending Failed: " . $e->getMessage());
        }
    }
    
}
