<?php

namespace App\Console\Commands;

use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\customer\Customer;
use App\Models\message_template\MessageTemplate;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Models\tenant\Tenant;
use App\Repositories\Focus\general\RosemailerRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Log;

class CustomerBirthdayNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:birthday_notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Customer Birthday Notification';

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
        $tenants = Tenant::where('status', 'Active')->get();

        foreach ($tenants as $tenant) {
            $setting = RecipientSetting::withoutGlobalScopes()
                ->where('ins', $tenant->id)
                ->where('type', 'birthdays')
                ->first();
            
            $messageTemplate = MessageTemplate::withoutGlobalScopes()
                ->where(['type' => 'birth_days', 'user_type' => 'customer', 'ins' => $tenant->id])
                ->first();

            $customers = Customer::withoutGlobalScopes(['ins'])
                ->where('ins', $tenant->id)
                ->whereMonth('dob', now()->month)
                ->whereDay('dob', now()->day)
                ->get();

            foreach ($customers as $customer) {
                    // dd($customer);
                $phoneNumber = $this->formatPhoneNumber($customer->phone);
                $message = $this->factorMessage($messageTemplate->text_message, $customer->company, $tenant->cname);
                
                if ($setting->email === 'yes') {
                    $this->sendEmail($customer, $message);
                }

                if ($setting->sms === 'yes') {
                    $this->sendSms($tenant, $customer, $phoneNumber, $message);
                }
            }
        }
    }

    private function formatPhoneNumber($phone)
    {
        $cleanedNumber = preg_replace('/\D/', '', $phone);
        return preg_match('/^01\d{8}$/', $cleanedNumber) ? '254' . substr($cleanedNumber, 1) : $cleanedNumber;
    }

    private function factorMessage($message, $customerName, $companyName)
    {
        return str_replace(['[Customer Name]', '[Company Name]'], [$customerName, $companyName], $message);
    }

    private function sendEmail($customer, $message)
    {
        $emailData = [
            'text' => $message,
            'subject' => 'Happy Birthday ' . $customer->company,
            'mail_to' => $customer->email,
        ];

        $emailResponse = (new RosemailerRepository($customer->ins))->send($emailData['text'], $emailData);
        $emailOutput = json_decode($emailResponse);

        if ($emailOutput->status === "Success") {
            SendEmail::create([
                'text_email' => $emailData['subject'],
                'subject' => $emailData['subject'],
                'user_emails' => $emailData['mail_to'],
                'user_ids' => $customer->id,
                'ins' => $customer->ins,
                'user_id' => $customer->ins,
                'status' => 'sent'
            ]);
        }
    }

    private function sendSms($tenant, $customer, $phoneNumber, $message)
    {
        $smsServer = SmsSetting::withoutGlobalScopes()->where('ins', $tenant->id)->first();
        if (!$smsServer || $smsServer->active != 1) return;

        $charCount = ceil(strlen($message) / 160);
        $costPer160 = 0.6;
        
        $smsData = [
            'subject' => $message,
            'user_type' => 'customer',
            'delivery_type' => 'now',
            'message_type' => 'bulk',
            'phone_numbers' => $phoneNumber,
            'sent_to_ids' => $customer->id,
            'characters' => $charCount,
            'cost' => $costPer160,
            'user_count' => 1,
            'total_cost' => $costPer160 * $charCount,
            'user_id' => $tenant->id,
            'ins' => $tenant->id,
        ];

        $sendSms = new SendSms();
        $sendSms->fill($smsData);
        $sendSms->save();
        
        $this->bulkSms($phoneNumber, $message, $sendSms, $tenant->id);
    }

    private function bulkSms($mobile, $textMessage, $sendSms, $ins)
    {
        $smsServer = SmsSetting::withoutGlobalScopes()->where('ins', $ins)->first();
        if (!$smsServer) return;

        $payload = [
            'senderID' => $smsServer->sender,
            'message' => $textMessage,
            'phones' => $mobile,
        ];

        try {
            $client = new Client();
            $response = $client->post('https://api.mobilesasa.com/v1/send/bulk', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $smsServer->username,
                ],
                'json' => $payload,
            ]);

            $content = json_decode($response->getBody()->getContents(), true);

            if (isset($content['bulkId'], $content['status'])) {
                SmsResponse::withoutGlobalScopes(['ins'])->create([
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
        } catch (RequestException $e) {
            Log::error("SMS Sending Failed: " . $e->getMessage());
        }
    }
}
