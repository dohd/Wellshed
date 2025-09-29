<?php

namespace App\Console\Commands;

use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\hrm\Hrm;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Models\tenant\Tenant;
use App\Models\tender\Tender;
use App\Repositories\Focus\general\RosemailerRepository;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;

class TenderFollowUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:tender_follow_ups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify on Next Tender Follow Up Date';

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
        try {
            $activeTenants = Tenant::where('status', 'Active')->get();
    
            foreach ($activeTenants as $tenant) {
                $tenders = Tender::withoutGlobalScopes()
                    ->where('ins', $tenant->id)
                    ->where('notify', 'yes')
                    ->whereHas('follow_ups', fn($q) => $q->withoutGlobalScopes()->whereDate('reminder_date', Carbon::today()))
                    ->get();
    
                if ($tenders->isEmpty()) {
                    continue;
                }
    
                $smsServer = SmsSetting::withoutGlobalScopes()->where('ins', $tenant->id)->first();
                $settings = RecipientSetting::withoutGlobalScopes()
                    ->where('ins', $tenant->id)
                    ->where('type', 'tender_notification')
                    ->first();
    
                foreach ($tenders as $tender) {
                    $this->processTender($tender, $tenant, $settings, $smsServer);
                }
            }
    
            $this->info(now() . ' SMS and Email sent Successfully!!');
        } catch (\Throwable $th) {
            $this->error(now() . ' ' . $th->getMessage() . ' at ' . $th->getFile() . ':' . $th->getLine());
        }
    }
    
    protected function processTender($tender, $tenant, $settings, $smsServer)
    {
        $followUp = $tender->follow_ups()->withoutGlobalScopes()->first();
        $recipients = $this->getValidRecipients($tender->team_member_ids);
    
        if (empty($recipients['phones'])) {
            return;
        }
    
        $subject = "From {$tenant->sms_email_name}, reminder: Follow up on the tender call for '{$tender->title}' today ({$followUp->reminder_date}). Ensure all details are confirmed.";
        $charCount = ceil(strlen($subject) / 160);
        $userCount = count($recipients['ids']);
        $usersCsv = implode(',', $recipients['ids']);
        $phonesCsv = implode(',', $recipients['phones']);
    
        if ($settings->email === 'yes') {
            $this->sendEmailNotification($tenant, $tender, $followUp->reminder_date, $recipients['emails'], $usersCsv);
        }
    
        if ($settings->sms === 'yes' && $smsServer->active == 1) {
            $smsData = [
                'subject' => $subject,
                'user_type' => 'employee',
                'delivery_type' => 'now',
                'message_type' => 'bulk',
                'phone_numbers' => $phonesCsv,
                'sent_to_ids' => $usersCsv,
                'characters' => $charCount,
                'cost' => 0.6,
                'user_count' => $userCount,
                'total_cost' => 0.6 * $charCount * $userCount,
                'user_id' => $tender->user_id,
                'ins' => $tender->ins,
            ];
    
            $sendSms = SendSms::create($smsData);
            $this->bulk_sms($phonesCsv, $subject, $sendSms, $tenant->id);
        }
    }
    
    protected function getValidRecipients($teamMemberIdsCsv)
    {
        $validPhones = [];
        $userIds = [];
        $emails = [];
        $pattern = '/^(0[17]\d{8}|254[17]\d{8})$/';
    
        $teamMemberIds = explode(',', $teamMemberIdsCsv);
        foreach ($teamMemberIds as $id) {
            $user = Hrm::withoutGlobalScopes()->where('status', 1)->find($id);
            if (!$user || !$user->meta) continue;
    
            $cleaned = preg_replace('/\D/', '', $user->meta->secondary_contact);
            if (!preg_match($pattern, $cleaned)) continue;
    
            $validPhones[] = preg_match('/^01\d{8}$/', $cleaned)
                ? '254' . substr($cleaned, 1)
                : $cleaned;
    
            $userIds[] = $user->id;
            $emails[] = $user->personal_email;
        }
    
        return [
            'phones' => $validPhones,
            'ids' => $userIds,
            'emails' => $emails
        ];
    }
    
    protected function sendEmailNotification($tenant, $tender, $reminderDate, $emails, $userIdsCsv)
    {
        if (empty($emails)) return;
    
        $subject = 'Reminder: Follow Up on Tender Call Today';
        $message = "
            <p>From {$tenant->sms_email_name},</p>
            <p>This is a reminder to follow up on the <strong>tender call</strong> for <strong>{$tender->title}</strong> scheduled for today, <strong>{$reminderDate}</strong>.</p>
            <p>Please ensure you reach out to the relevant contacts, confirm any pending details, and document the discussion accordingly.</p>
            <p>If you need any assistance or additional information, do not hesitate to reach out.</p>
            <p>Best regards,</p>
        ";
    
        $mailTo = array_shift($emails);
        $emailInput = [
            'text' => $message,
            'subject' => $subject,
            'email' => $emails,
            'mail_to' => $mailTo,
        ];
    
        $response = (new RosemailerRepository($tender->ins))->send_group($message, $emailInput);
        $result = json_decode($response);
    
        if ($result->status === "Success") {
            SendEmail::create([
                'text_email' => $message,
                'subject' => $subject,
                'user_emails' => $mailTo,
                'user_ids' => $userIdsCsv,
                'ins' => $tender->ins,
                'user_id' => $tender->user_id,
                'status' => 'sent'
            ]);
        }
    }
    

    public function bulk_sms($mobile, $text_message, $send_sms_id, $ins)
    {
        $sms_server = SmsSetting::withoutGlobalScopes()->where('ins', $ins)->first();
        // Prepare the payload
        $payload = [
            'senderID' => $sms_server->sender,
            'message' => $text_message,
            'phones' => $mobile,
        ];
        

        // Use GuzzleHTTP to send the message
        try {
            $client = new \GuzzleHttp\Client();
            $apiToken = $sms_server->username;
            
            // Make the async request to the API
            $promise = $client->postAsync('https://api.mobilesasa.com/v1/send/bulk', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiToken,
                ],
                'json' => $payload,
            ]);

            // Wait for the response of the async request
            $response = $promise->wait();

            // Handle the successful response
            $body = $response->getBody();
            $content = json_decode($body->getContents(), true); // Convert to array
            // dd($content);

            if (isset($content['bulkId']) && isset($content['status'])) {
                
                $phone_numbers = explode(',', $mobile);
                $data = [
                    'send_sms_id' => $send_sms_id->id,
                    'message_response_id' => $content['bulkId'],
                    'status' => 1,
                    'message_type' => 'bulk',
                    'phone_number_count' => count($phone_numbers),
                    'response_code' => $content['responseCode'] ?? null,
                    'ins' => $ins,
                    'user_id' => $ins
                ];
                

                // Save the SMS response
                // dd($data);
                try {
                    $user = auth()->user();
                    if ($user) {
                        $data['user_id'] = $user->id;
                        $data['ins'] = $user->ins;
                    } else {
                        // Handle unauthenticated case
                        $data['user_id'] = $ins;
                        $data['ins'] = $ins;
                    }
                    SmsResponse::withoutGlobalScopes(['ins'])->create($data);
                } catch (\Throwable $th) {
                    //throw $th;
                    \Log::error("An error occurred: " . $th->getMessage());
                }

                // Log the response
                echo "SMS Sent Successfully: " . json_encode($content) . "\n";
            } else {
                echo "Unexpected response format: " . json_encode($content) . "\n";
            }

        } catch (RequestException $e) {
            // Handle request exceptions (e.g., network errors)
            echo "Failed to send SMS: " . $e->getMessage() . "\n";

            if ($e->hasResponse()) {
                $errorResponse = $e->getResponse();
                $errorBody = $errorResponse->getBody()->getContents();
                echo "Error Response: " . $errorBody . "\n";
            }
        } catch (\Exception $e) {
            // Handle any other errors
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}
