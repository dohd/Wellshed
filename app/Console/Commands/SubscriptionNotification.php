<?php

namespace App\Console\Commands;

use App\Models\Company\Company;
use App\Models\Company\EmailSetting;
use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\hrm\Hrm;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Models\tenant\Tenant;
use App\Repositories\Focus\general\RosemailerRepository;
use DateInterval;
use DateTime;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SubscriptionNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:subscription-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify Subscribers on Their Subscription Status';

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

        $tenants = Tenant::orderBy('cname')->where('is_tenant', 1)->get();

        foreach ($tenants as $business) {

            $today = new DateTime();
            $billingDate = new DateTime($business->billing_date);
            if ($business->grace_days > 0) $cutoffDate = (clone $billingDate)->add(new DateInterval('P' . intval($business->grace_days) . 'D'));
            else $cutoffDate = clone $billingDate;

            $timeToCutoff = (clone $cutoffDate)->diff(clone $today);
            $setting = RecipientSetting::withoutGlobalScopes()->where('ins',2)->where(['type' => 'tenant_subscription'])->first();
            $recipient_ids = explode(',', $setting->recipients);
            $phone_numbers = [];
            $user_ids = [];
            $emails = [];
            $pattern = '/^(07\d{8}|2547\d{8})$/';
            foreach($recipient_ids as $recipient_id){
                $user = Hrm::withoutGlobalScopes()->where('status',1)->where('ins', $business->id)->find($recipient_id);
                // dd($user);
                if(!$user) continue;
                $user_meta = $user->meta()->withoutGlobalScopes()->first();
                if($user_meta){
                    $cleanedNumber = preg_replace('/\D/', '', $user_meta->primary_contact);
                    if (preg_match($pattern, $cleanedNumber)) {
                        $phone_numbers[] = $cleanedNumber;
                        $user_ids[] = $user->id;
                        $emails[] = $user->email;
                    }
                }
            }
            $contacts = implode(',', $phone_numbers);
            $users = implode(',', $user_ids);
            $company = Company::find(2);
            $cost_per_160 = 0.6;
            
            $data = [
                'user_type' =>'employee',
                'delivery_type' =>'now',
                'message_type' => 'bulk',
                'phone_numbers' => $contacts,
                'sent_to_ids' => $users,
                'characters' => '',
                'cost' => '',
                'user_count' => '',
                'total_cost' => '',
                'user_id' => 2,
                'ins' => 2,

            ];
            


            if ($business->billing_date && $today <= $cutoffDate ) {

                if ($timeToCutoff->days === 7){
                    $subject = "Dear {$business->cname},
                        We hope this message finds you well. This is a friendly reminder that your subscription package with {$company->cname} is set for renewal in 7 days time.
                        To avoid any interruptions in service, please ensure that the payment is made before {$cutoffDate->format('g:ia | l, jS F, Y')}. If payment is not received by the due date, your subscription may be deactivated temporarily until payment is processed. Go to Client area > Subscriptions > To pay or redeem your loyalty points or extend your cut off date.";
                    $data['subject'] = $subject;
                    $totalCharacters = strlen($subject);
                    $charCount = ceil($totalCharacters/160);
                    $count_users = count($user_ids);
                    $data['characters'] = $charCount;
                    $data['cost'] = $cost_per_160;
                    $data['user_count'] = $count_users;
                    $data['total_cost'] = $cost_per_160*$charCount*$count_users;
                    if($setting->sms == 'yes'){
                        $send_sms = new SendSms();
                        $send_sms->fill($data);
                        $send_sms->user_id = 2;  // Manually assign user_id
                        $send_sms->ins = 2;
                        $send_sms->save();
                        $this->bulk_sms($data['phone_numbers'], $data['subject'], $send_sms, $company->id);
                    }

                    if($setting->email == 'yes'){
                        $mail_to = array_shift($emails);
                        $others = $emails;
                        //Send EMAILs
                        $email_input = [
                            'text' => $subject,
                            'subject' => 'Important Subscription Renewal Reminder',
                            'email' => $others,
                            'mail_to' => $mail_to
                        ];
                        $email = (new RosemailerRepository($company->id))->send_group($email_input['text'], $email_input);
                        $email_output = json_decode($email);
                        if ($email_output->status === "Success"){

                            $email_data = [
                                'text_email' => $email_input['text'],
                                'subject' => $email_input['subject'],
                                'user_emails' => $email_input['mail_to'],
                                'user_ids' => $users,
                                'ins' => $company->id,
                                'user_id' => $company->id,
                                'status' => 'sent'
                            ];
                            SendEmail::create($email_data);
                        }
                    }

                    //Log::info($business->cname . ": You have " . 7 . " days to cutoff");
                }
                else if ($timeToCutoff->days === 3){
                    $subject = "Dear {$business->cname},
                        We hope this message finds you well. This is a friendly reminder that your subscription package with {$company->cname} is set for renewal in 3 days time.
                        To avoid any interruptions in service, please ensure that the payment is made before {$cutoffDate->format('g:ia | l, jS F, Y')}. If payment is not received by the due date, your subscription may be deactivated temporarily until payment is processed. Go to Client area > Subscriptions > To pay or redeem your loyalty points or extend your cut off date.";
                    $data['subject'] = $subject;
                    $totalCharacters = strlen($subject);
                    $charCount = ceil($totalCharacters/160);
                    $count_users = count($user_ids);
                    $data['characters'] = $charCount;
                    $data['cost'] = $cost_per_160;
                    $data['user_count'] = $count_users;
                    $data['total_cost'] = $cost_per_160*$charCount*$count_users;
                    if($setting->sms == 'yes'){
                        $send_sms = new SendSms();
                        $send_sms->fill($data);
                        $send_sms->user_id = 2;  // Manually assign user_id
                        $send_sms->ins = 2;
                        $send_sms->save();
                        $this->bulk_sms($data['phone_numbers'], $data['subject'], $send_sms, $company->id);
                    }
                    if($setting->email == 'yes'){
                        $mail_to = array_shift($emails);
                        $others = $emails;
                        //Send EMAILs
                        $email_input = [
                            'text' => $subject,
                            'subject' => 'Important Subscription Renewal Reminder',
                            'email' => $others,
                            'mail_to' => $mail_to
                        ];
                        $email = (new RosemailerRepository($company->id))->send_group($email_input['text'], $email_input);
                        $email_output = json_decode($email);
                        if ($email_output->status === "Success"){

                            $email_data = [
                                'text_email' => $email_input['text'],
                                'subject' => $email_input['subject'],
                                'user_emails' => $email_input['mail_to'],
                                'user_ids' => $users,
                                'ins' => $company->id,
                                'user_id' => $company->id,
                                'status' => 'sent'
                            ];
                            SendEmail::create($email_data);
                        }
                    }
                    //Log::info($business->cname . ": You have " . 3 . " days to cutoff");
                }
                else if ($timeToCutoff->days === 1){

                    $subject = "Dear {$business->cname},
                        We hope this message finds you well. This is a friendly reminder that your subscription package with {$company->cname} is set for renewal in 1 day(s) time.
                        To avoid any interruptions in service, please ensure that the payment is made before {$cutoffDate->format('g:ia | l, jS F, Y')}. If payment is not received by the due date, your subscription may be deactivated temporarily until payment is processed. Go to Client area > Subscriptions > To pay or redeem your loyalty points or extend your cut off date.";
                    $data['subject'] = $subject;
                    $totalCharacters = strlen($subject);
                    $charCount = ceil($totalCharacters/160);
                    $count_users = count($user_ids);
                    $data['characters'] = $charCount;
                    $data['cost'] = $cost_per_160;
                    $data['user_count'] = $count_users;
                    $data['total_cost'] = $cost_per_160*$charCount*$count_users;
                    if($setting->sms == 'yes'){
                        $send_sms = new SendSms();
                        $send_sms->fill($data);
                        $send_sms->user_id = 2;  // Manually assign user_id
                        $send_sms->ins = 2;
                        $send_sms->save();
                        $this->bulk_sms($data['phone_numbers'], $data['subject'], $send_sms, $company->id);
                    }
                    if($setting->email == 'yes'){
                        $mail_to = array_shift($emails);
                        $others = $emails;
                        //Send EMAILs
                        $email_input = [
                            'text' => $subject,
                            'subject' => 'Important Subscription Renewal Reminder',
                            'email' => $others,
                            'mail_to' => $mail_to
                        ];
                        $email = (new RosemailerRepository($company->id))->send_group($email_input['text'], $email_input);
                        $email_output = json_decode($email);
                        if ($email_output->status === "Success"){

                            $email_data = [
                                'text_email' => $email_input['text'],
                                'subject' => $email_input['subject'],
                                'user_emails' => $email_input['mail_to'],
                                'user_ids' => $users,
                                'ins' => $company->id,
                                'user_id' => $company->id,
                                'status' => 'sent'
                            ];
                            SendEmail::create($email_data);
                        }
                    }
                    //Log::info($business->cname . ": You have " . 1 . " day to cutoff");
                }
                else if ($timeToCutoff->days === 0){
                    //Send to office
                    $message = "Dear {$company->cname},\n";
                    $message .= "We would like to inform you that the subscription for your customer, {$business->cname}, is set to expire today, {$cutoffDate->format('g:ia | l, jS F, Y')}.\n\n";
                    $office_subject = $message;
                    $office_input = $data;
                    $email_setting = EmailSetting::withoutGlobalScopes()->where('ins', $company->id)->first();
                    $office_input['phone_numbers'] = $email_setting->office_number;
                    $office_input['subject'] = $office_subject;
                    $totalCharacters = strlen($office_subject);
                    $character_count = ceil($totalCharacters/160);
                    $users_count = count($user_ids);
                    $office_input['characters'] = $character_count;
                    $office_input['cost'] = $cost_per_160;
                    $office_input['user_count'] = $users_count;
                    $office_input['total_cost'] = $cost_per_160*$character_count*$users_count;
                    if($email_setting->office_number){
                        $office_sms = new SendSms();
                        $office_sms->fill($office_input);
                        $office_sms->user_id = 2;  // Manually assign user_id
                        $office_sms->ins = 2;
                        $office_sms->save();
                        $this->bulk_sms($office_input['phone_numbers'], $office_input['subject'], $office_sms, $company->id);
                    }
                    $subject = "Dear {$business->cname}, We hope this message finds you well. This is a friendly reminder that your subscription package with {$company->cname} is set to expire today. To avoid any interruptions in service, please ensure that the payment is made before ({$cutoffDate->format('g:ia | l, jS F, Y')}). If payment is not received by the due date, your subscription may be deactivated temporarily until payment is processed. Go to Client area > Subscriptions > To pay or redeem your loyalty points or extend your cut off date.";
                    $data['subject'] = $subject;
                    $totalCharacters = strlen($subject);
                    $charCount = ceil($totalCharacters/160);
                    $count_users = count($user_ids);
                    $data['characters'] = $charCount;
                    $data['cost'] = $cost_per_160;
                    $data['user_count'] = $count_users;
                    $data['total_cost'] = $cost_per_160*$charCount*$count_users;
                    if($setting->sms == 'yes'){
                        $send_sms = new SendSms();
                        $send_sms->fill($data);
                        $send_sms->user_id = 2;  // Manually assign user_id
                        $send_sms->ins = 2;
                        $send_sms->save();
                        $this->bulk_sms($data['phone_numbers'], $data['subject'], $send_sms, $company->id);
                    }
                    if($setting->email == 'yes'){
                        $mail_to = array_shift($emails);
                        $others = $emails;
                        //Send EMAILs
                        $email_input = [
                            'text' => $subject,
                            'subject' => 'Important Subscription Renewal Reminder',
                            'email' => $others,
                            'mail_to' => $mail_to
                        ];
                        $email = (new RosemailerRepository($company->id))->send_group($email_input['text'], $email_input);
                        $email_output = json_decode($email);
                        if ($email_output->status === "Success"){

                            $email_data = [
                                'text_email' => $email_input['text'],
                                'subject' => $email_input['subject'],
                                'user_emails' => $email_input['mail_to'],
                                'user_ids' => $users,
                                'ins' => $company->id,
                                'user_id' => $company->id,
                                'status' => 'sent'
                            ];
                            SendEmail::create($email_data);
                        }
                    }
                    //Log::info($business->cname . ": Your subscription will expire today");
                }
            }
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
