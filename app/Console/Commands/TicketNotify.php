<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use App\Models\lead\Lead;
use Illuminate\Support\Facades\DB;
use App\Notifications\LeadNotification;
use Carbon\Carbon;
use App\Models\hrm\Hrm;
use App\Models\Access\User\User;
use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Repositories\Focus\general\RosemailerRepository;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Notification;
use Illuminate\Console\Command;

class TicketNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ticket Notification';

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

        Company::where('status', 'Active')->chunk(50, function ($tenants) use ($today) {
            foreach ($tenants as $tenant) {
                $leads = Lead::whereDate('reminder_date', '<=', $today)
                    ->whereDate('exact_date', '>=', $today)
                    ->withoutGlobalScopes()
                    ->where('ins', $tenant->id)
                    ->get();

                if ($leads->isEmpty()) continue;
                // dd($leads);

                $users = User::whereHas('user_associated_permission', function ($query) {
                    $query->where('name', 'create-lead');
                })->withoutGlobalScopes()
                ->where('ins', $tenant->id)
                ->get();

                $smsServer = SmsSetting::withoutGlobalScopes()->where('ins', $tenant->id)->first();
                $emailSetting = RecipientSetting::withoutGlobalScopes()
                    ->where('type', 'ticket_notification')
                    ->where('ins', $tenant->id)
                    ->first();

                foreach ($leads as $lead) {
                    $employee = Hrm::withoutGlobalScopes()
                        ->where('ins', $tenant->id)
                        ->where('id', $lead->user_id)
                        ->where('status', 1)
                        ->first();

                    if ($employee) {
                        $this->send_message($employee, $tenant, $smsServer, $emailSetting, $lead);
                    }

                    foreach ($users as $user) {
                        $user->notify(new LeadNotification($lead));
                    }
                }
            }
        });
    }

    public function send_message($user, $company, $smsServer, $setting, $lead)
    {
        $phoneNumber = $this->formatPhoneNumber(optional($user->meta)->secondary_contact);
        $userId = $user->id;
        $lead_no = gen4tid('TKT-',$lead->reference);
        $lead_title = $lead->title;

        if ($setting->email === 'yes') {
            $email_input = [
                'text' => 'Dear '. $user->fullname .',

                This is a friendly reminder that the ticket you created, titled "<strong>'.$lead_no .'-'. $lead_title.'</strong>", is approaching its exact date on <strong>'.$lead->exact_date.'</strong>.

                Please review the ticket and take any necessary steps to ensure it is resolved on time.

                If you have any questions or need assistance, feel free to reach out to your supervisor or the support team.

                Best regards,  
                '.$company->cname,
                'subject' => 'Reminder: Upcoming Ticket - '.$lead_no,
                'mail_to' => $user->personal_email,
            ];
            $email = (new RosemailerRepository($user->ins))->send($email_input['text'], $email_input);
            $response = json_decode($email);
            if ($response->status === "Success") {
                SendEmail::create([
                    'text_email' => $email_input['subject'],
                    'subject' => $email_input['subject'],
                    'user_emails' => $email_input['mail_to'],
                    'user_ids' => $userId,
                    'ins' => $user->ins,
                    'user_id' => $user->ins,
                    'status' => 'sent',
                ]);
            }
        }

        if ($setting->sms === 'yes' && $smsServer && $smsServer->active == 1 && $phoneNumber) {
            $subject = "From {$company->sms_email_name}: Hi {$user->fullname}, reminder: The exact date for your ticket {$lead_no} - {$lead_title} is approaching on {$lead->exact_date}. Please take any necessary action.";
            $charCount = ceil(strlen($subject) / 160);
            $costPer160 = 0.6;

            $data = [
                'subject' => $subject,
                'user_type' => 'employee',
                'delivery_type' => 'now',
                'message_type' => 'bulk',
                'phone_numbers' => $phoneNumber,
                'sent_to_ids' => $userId,
                'characters' => $charCount,
                'cost' => $costPer160,
                'user_count' => 1,
                'total_cost' => $costPer160 * $charCount,
                'user_id' => $company->id,
                'ins' => $company->id,
            ];

            $send_sms = SendSms::create($data);
            $this->bulk_sms($phoneNumber, $subject, $send_sms, $company->id);
        }
    }

    private function formatPhoneNumber($number)
    {
        $clean = preg_replace('/\D/', '', $number);
        if (preg_match('/^0[17]\d{8}$/', $clean)) {
            return '254' . substr($clean, 1);
        } elseif (preg_match('/^254[17]\d{8}$/', $clean)) {
            return $clean;
        }
        return null;
    }

    public function bulk_sms($mobile, $text_message, $send_sms, $ins)
    {
        $smsServer = SmsSetting::withoutGlobalScopes()->where('ins', $ins)->first();
        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->post('https://api.mobilesasa.com/v1/send/bulk', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $smsServer->username,
                ],
                'json' => [
                    'senderID' => $smsServer->sender,
                    'message' => $text_message,
                    'phones' => $mobile,
                ],
            ]);

            $content = json_decode($response->getBody(), true);

            if (!empty($content['bulkId']) && !empty($content['status'])) {
                SmsResponse::withoutGlobalScopes(['ins'])->create([
                    'send_sms_id' => $send_sms->id,
                    'message_response_id' => $content['bulkId'],
                    'status' => 1,
                    'message_type' => 'bulk',
                    'phone_number_count' => count(explode(',', $mobile)),
                    'response_code' => $content['responseCode'] ?? null,
                    'ins' => $ins,
                    'user_id' => optional(auth()->user())->id ?? $ins,
                ]);
            } else {
                \Log::warning('Unexpected SMS response', $content);
            }
        } catch (RequestException $e) {
            \Log::error("SMS sending failed: " . $e->getMessage());
            if ($e->hasResponse()) {
                \Log::error("SMS error response: " . $e->getResponse()->getBody()->getContents());
            }
        } catch (\Exception $e) {
            \Log::error("Bulk SMS error: " . $e->getMessage());
        }
    }

}
