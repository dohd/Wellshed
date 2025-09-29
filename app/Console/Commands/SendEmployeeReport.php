<?php

namespace App\Console\Commands;

use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\hrm\Hrm;
use App\Models\labour_allocation\LabourAllocationItem;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Repositories\Focus\general\RosemailerRepository;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;

class SendEmployeeReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:employee_summary_report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Employee Report';

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
            $this->employee_summary_report();
        } catch (\Throwable $th) {
            //throw $th;
            $this->error(now() . ' ' . $th->getMessage() . ' at ' . $th->getFile() . ':' . $th->getLine());
        }
        $this->info(now() . ' Employee Weekly/Monthly Report: ');
    }

    public function employee_summary_report()
    {

        $companies = Company::all();
        foreach ($companies as $company) {
            $users =  Hrm::withoutGlobalScopes()->where('ins', $company->id)->where('status', 1)->get();
            $sms_server = SmsSetting::withoutGlobalScopes()->where('ins', $company->id)->first();
            $pattern = '/^(0[17]\d{8}|254[17]\d{8})$/';
            $setting = RecipientSetting::withoutGlobalScopes()->where('type', 'technician_report')->where('ins', $company->id)->first();
            foreach ($users as $user) {
                $q = LabourAllocationItem::withoutGlobalScopes()->where('employee_id', $user->id)
                    ->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->get();
                if (count($q) > 0) {
                    $employeeId = $user->id; // Example employee ID
                    $month = now()->month;
                    $year = now()->year;
                    // dd(route('employee_summary_report_api'));

                    $link = route('employee_summary_report', [
                        'user_id' => $employeeId,
                        'month' => $month,
                        'year' => $year,
                    ]);

                    if ($user->meta) {
                        $cleanedNumber = preg_replace('/\D/', '', $user->meta->primary_contact);
                        if (preg_match($pattern, $cleanedNumber)) {
                            if (preg_match('/^01\d{8}$/', $cleanedNumber)) {
                                $phone_number = '254' . substr($cleanedNumber, 1); // Replace '0' with '254'
                            } else {
                                $phone_number = $cleanedNumber;
                            }
                            $user_id = $user->id;
                            $links = $link;
                        }
                    }
                    $email_input = [
                        'text' => 'The link to your report is ' . $link,
                        'subject' => 'Employee Summary Report',
                        'mail_to' => $user->email,
                    ];
                    if ($setting->email == 'yes') {
                        $email = (new RosemailerRepository($user->ins))->send($email_input['text'], $email_input);
                        $email_output = json_decode($email);
                        if ($email_output->status === "Success") {
                            $email_data = [
                                'text_email' => $email_input['subject'],
                                'subject' => $email_input['subject'],
                                'user_emails' => $email_input['mail_to'],
                                'user_ids' => $user->id,
                                'ins' => $user->ins,
                                'user_id' => $user->ins,
                                'status' => 'sent'
                            ];
                            SendEmail::create($email_data);
                        }
                    }


                    // $contacts = implode(',', $phone_numbers);
                    // $users = implode(',', $user_ids);
                    $subject = "From {$company->sms_email_name}: Please find Link to your weekly or monthly report " . $links;
                    $cost_per_160 = 0.6;
                    $totalCharacters = strlen($subject);
                    $charCount = ceil($totalCharacters / 160);
                    // $count_users = count($user_ids);
                    $data = [
                        'subject' => $subject,
                        'user_type' => 'employee',
                        'delivery_type' => 'now',
                        'message_type' => 'bulk',
                        'phone_numbers' => $phone_number,
                        'sent_to_ids' => $user_id,
                        'characters' => $charCount,
                        'cost' => $cost_per_160,
                        'user_count' => 1,
                        'total_cost' => $cost_per_160 * $charCount * 1,
                        'user_id' => $company->id,
                        'ins' => $company->id,

                    ];
                    if ($setting->sms == 'yes' && $sms_server->active == 1) {
                        $send_sms = new SendSms();
                        $send_sms->fill($data);
                        $send_sms->user_id = $company->id;  // Manually assign user_id
                        $send_sms->ins = $company->id;
                        $send_sms->save();
                        $this->bulk_sms($data['phone_numbers'], $data['subject'], $send_sms, $company->id);
                    }
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
