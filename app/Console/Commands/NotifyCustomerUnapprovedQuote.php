<?php

namespace App\Console\Commands;

use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\hrm\Hrm;
use App\Models\quote\Quote;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Repositories\Focus\general\RosemailerRepository;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;

class NotifyCustomerUnapprovedQuote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:customer_unapproved_quote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify the customer of Unapproved Quotes';

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
        // All tenants
        $companies = Company::all();
        foreach ($companies as $company)
        {
            $quotes = Quote::withoutGlobalScopes()->whereNull('approved_by')->whereDate('unapproved_reminder_date', Carbon::today())->where('ins',$company->id)->get();
            $setting = RecipientSetting::withoutGlobalScopes()->where('type', 'unapproved_quote')->where('ins', $company->id)->first();
            $pattern = '/^(07\d{8}|2547\d{8})$/';
            foreach($quotes as $quote){
                //customer
                $customer = $quote->customer()->withoutGlobalScopes()->first();
                $lead = $quote->lead()->withoutGlobalScopes()->first();
                $phone_number = '';
                if ($customer) {
                    $cleanedNumber = preg_replace('/\D/', '', $customer->phone);
                    if (preg_match($pattern, $cleanedNumber)) {
                        $phone_number = $cleanedNumber;
                        $customer_id = $customer->id;
                    }
                }else if ($lead){
                    $cleanedNumber = preg_replace('/\D/', '', $lead->client_contact);
                    if (preg_match($pattern, $cleanedNumber)) {
                        $phone_number = $cleanedNumber;
                        $customer_id = $lead->ins;
                    }
                }
                $recipient_ids = explode(',', $setting->recipients);
                $contact = [];

                foreach($recipient_ids as $recipient_id){
                    $user = Hrm::withoutGlobalScopes()->where('status',1)->where('ins', $company->id)->find($recipient_id);
                    if(!$user) continue;
                    $user_meta = $user->meta()->withoutGlobalScopes()->first();
                    if($user_meta){
                        $cleanedNumber = preg_replace('/\D/', '', $user_meta->primary_contact);
                        if (preg_match($pattern, $cleanedNumber)) {
                            $contact[] = $cleanedNumber;
                        }
                    }
                }
                $contacts = implode(',', $contact);

                $tid = $quote->bank_id > 0  ? gen4tid('PI-',$quote->tid) : gen4tid('QT-',$quote->tid);

                $message = "Dear {$customer->company},\n\nWe hope you're doing well! We wanted to kindly remind you that your quote {$tid} is still awaiting your review and approval. If everything looks good on your end, we would appreciate your approval at your earliest convenience to help move things forward smoothly.\n\n
                If you have any questions or require any clarifications, please feel free to reach out.\n\nLooking forward to your response.\n\nBest regards,\n{$company->cname}\n{$contacts}";

                $email_input = [
                    'text' => $message,
                    'subject' => 'Reminder: Pending Quote Approval',
                    'mail_to' => $customer->email,
                ];
                // dd($setting->email)
                if ($setting->email == 'yes') {
                    $email = (new RosemailerRepository($customer->ins))->send($email_input['text'], $email_input);
                    $email_output = json_decode($email);
                    if ($email_output->status === "Success") {
                        $email_data = [
                            'text_email' => $email_input['subject'],
                            'subject' => $email_input['subject'],
                            'user_emails' => $email_input['mail_to'],
                            'user_ids' => $customer->id,
                            'ins' => $customer->ins,
                            'user_id' => $customer->ins,
                            'status' => 'sent'
                        ];
                        SendEmail::create($email_data);
                    }
                }


                $phone = $phone_number;
                // $users = implode(',', $user_ids);
                $subject = "From {$company->sms_email_name}: Dear {$customer->company}, your quote {$tid} is still awaiting your review. If everything looks good, we’d appreciate your approval at your convenience to ensure a smooth process. Need assistance? Feel free to reach out. {$contacts}.";
                $cost_per_160 = 0.6;
                $totalCharacters = strlen($subject);
                $charCount = ceil($totalCharacters / 160);
                // $count_users = count($user_ids);
                $data = [
                    'subject' => $subject,
                    'user_type' => 'customer',
                    'delivery_type' => 'now',
                    'message_type' => 'bulk',
                    'phone_numbers' => $phone,
                    'sent_to_ids' => $customer->id,
                    'characters' => $charCount,
                    'cost' => $cost_per_160,
                    'user_count' => 1,
                    'total_cost' => $cost_per_160 * $charCount * 1,
                    'user_id' => $company->id,
                    'ins' => $company->id,

                ];
                if ($setting->sms == 'yes') {
                    $send_sms = new SendSms();
                    $send_sms->fill($data);
                    $send_sms->user_id = $company->id;  // Manually assign user_id
                    $send_sms->ins = $company->id;
                    $send_sms->save();
                    $this->bulk_sms($data['phone_numbers'], $data['subject'], $send_sms, $company->id);
                }
            }
        }
        //get unapproved quotes
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
