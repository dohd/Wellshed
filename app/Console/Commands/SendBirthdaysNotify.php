<?php

namespace App\Console\Commands;

use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\hrm\Hrm;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Models\tenant\Tenant;
use App\Repositories\Focus\general\RosemailerRepository;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;

class SendBirthdaysNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:birthday_wishes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending Notification for Birthdays';

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
        $tenants = Tenant::orderBy('cname')->get();
        foreach ($tenants as $tenant) {
            $setting = RecipientSetting::withoutGlobalScopes()->where('ins', $tenant->id)->where(['type' => 'birthdays'])->first();
            $users = Hrm::withoutGlobalScopes(['ins'])->where('ins', $tenant->id)->where('status', 1)
                ->whereHas('meta', function ($q) {
                    $q->withoutGlobalScopes();
                    $q->whereMonth('dob', now()->month)
                        ->whereDay('dob', now()->day);
                })

                ->get();

            if ($users->isEmpty()) {
                $this->info('No birthdays today.');
                return 0;
            }
            $pattern = '/^(07\d{8}|2547\d{8})$/';
            foreach ($users as $user) {
                // Send email
                if ($user->meta) {
                    $cleanedNumber = preg_replace('/\D/', '', $user->meta->primary_contact);
                    if (preg_match($pattern, $cleanedNumber)) {
                        $phone_number = $cleanedNumber;
                        $user_ids = $user->id;
                        $emails = $user->email;
                    }
                }
                // dd($user_ids);
                $subject = "Dear {$user->fullname}, Happy Birthday to you. We appreciate you from {$tenant->cname} Fratanity, have a nice day.";
                $cost_per_160 = 0.6;
                $totalCharacters = strlen($subject);
                $charCount = ceil($totalCharacters / 160);
                $count_users = $user_ids;
                $data = [
                    'subject' => $subject,
                    'user_type' => 'employee',
                    'delivery_type' => 'now',
                    'message_type' => 'bulk',
                    'phone_numbers' => $phone_number,
                    'sent_to_ids' => $user_ids,
                    'characters' => $charCount,
                    'cost' => $cost_per_160,
                    'user_count' => $count_users,
                    'total_cost' => $cost_per_160 * $charCount * $count_users,
                    'user_id' => $tenant->id,
                    'ins' => $tenant->id,

                ];
                if ($setting->sms == 'yes') {
                    $send_sms = new SendSms();
                    $send_sms->fill($data);
                    $send_sms->user_id = $tenant->id;  // Manually assign user_id
                    $send_sms->ins = $tenant->id;
                    $send_sms->save();
                    $this->bulk_sms($data['phone_numbers'], $data['subject'], $send_sms, $tenant->id);
                }
                if ($setting->email == 'yes') {
                    $mail_to = $emails;
                    $others = $emails;
                    //Send EMAILs
                    $email_input = [
                        'text' => $subject,
                        'subject' => 'Happy Birthday!',
                        // 'email' => $others,
                        'mail_to' => $mail_to
                    ];
                    $email = (new RosemailerRepository($user->ins))->send($email_input['text'], $email_input);
                    $email_output = json_decode($email);
                    if ($email_output->status === "Success") {

                        $email_data = [
                            'text_email' => $email_input['text'],
                            'subject' => $email_input['subject'],
                            'user_emails' => $email_input['mail_to'],
                            'user_ids' => $users,
                            'ins' => $tenant->id,
                            'user_id' => $tenant->id,
                            'status' => 'sent'
                        ];
                        SendEmail::create($email_data);
                    }
                }

                $recipients_ids = explode(',', $setting->recipients);
                $contanct_numbers = [];
                $hrm_ids = [];
                $receipient_emails = [];
                $pattern = '/^(07\d{8}|2547\d{8})$/';
                foreach ($recipients_ids as $recipient_id) {
                    $hrm = Hrm::withoutGlobalScopes()->where('status', 1)->find($recipient_id);
                    if ($hrm->meta) {
                        $cleanedNumber = preg_replace('/\D/', '', $hrm->meta->primary_contact);
                        if (preg_match($pattern, $cleanedNumber)) {
                            $contanct_numbers[] = $cleanedNumber;
                            $hrm_ids[] = $hrm->id;
                            $receipient_emails[] = $hrm->email;
                        }
                    }
                }
                $contacts = implode(',', $contanct_numbers);
                $hrms = implode(',', $hrm_ids);
                $this->send_sms_and_email($tenant, $setting, $contacts, $hrms, $hrm_ids, $user,$phone_number,$receipient_emails);

                $this->info("Birthday wish sent to: {$user->email}");
            }
            //users to notify

        }

        return 0;
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

    public function send_sms_and_email($tenant, $setting, $contacts, $hrms, $hrm_ids, $birthday_user,$phone_number,$receipient_emails)
    {
        // dd($hrms, $hrm_ids);
        $subject = "From {$tenant->sms_email_name}: Please Note that today is {$birthday_user->fullname} Birthday, please wish them Happy Birthday. Their contact is: {$phone_number}";
        $cost_per_160 = 0.6;
        $totalCharacters = strlen($subject);
        $charCount = ceil($totalCharacters / 160);
        $count_users = count($hrm_ids);
        $data = [
            'subject' => $subject,
            'user_type' => 'employee',
            'delivery_type' => 'now',
            'message_type' => 'bulk',
            'phone_numbers' => $contacts,
            'sent_to_ids' => $hrms,
            'characters' => $charCount,
            'cost' => $cost_per_160,
            'user_count' => $count_users,
            'total_cost' => $cost_per_160 * $charCount * $count_users,
            'user_id' => $tenant->id,
            'ins' => $tenant->id,

        ];
        if ($setting->sms == 'yes') {
            $send_sms = new SendSms();
            $send_sms->fill($data);
            $send_sms->user_id = $tenant->id;  // Manually assign user_id
            $send_sms->ins = $tenant->id;
            $send_sms->save();
            $this->bulk_sms($data['phone_numbers'], $data['subject'], $send_sms, $tenant->id);
        }
        if ($setting->email == 'yes') {
            $mail_to = array_shift($receipient_emails);
            $others = $receipient_emails;
            //Send EMAILs
            $email_input = [
                'text' => $subject,
                'subject' => 'Happy Birthday!',
                'email' => $others,
                'mail_to' => $mail_to
            ];
            $email = (new RosemailerRepository($tenant->id))->send_group($email_input['text'], $email_input);
            $email_output = json_decode($email);
            if ($email_output->status === "Success") {

                $email_data = [
                    'text_email' => $email_input['text'],
                    'subject' => $email_input['subject'],
                    'user_emails' => $email_input['mail_to'],
                    'user_ids' => $hrms,
                    'ins' => $tenant->id,
                    'user_id' => $tenant->id,
                    'status' => 'sent'
                ];
                SendEmail::create($email_data);
            }
        }
    }
}
