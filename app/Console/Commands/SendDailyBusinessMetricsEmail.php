<?php

namespace App\Console\Commands;

use App\Models\Company\Company;
use App\Models\Company\EmailSetting;
use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\dailyBusinessMetric\DailyBusinessMetric;
use App\Models\dailyBusinessMetric\DbmDisplayOption;
use App\Models\hrm\Hrm;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Repositories\Focus\general\RosemailerRepository;
use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendDailyBusinessMetricsEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-daily-business-metrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a daily business metrics email every morning at 8 AM';

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
            // DB::beginTransaction();
            $this->get_settings();
            // DB::commit();
            $this->info(now() .' Initiate (GET) Callbacks SMS: ');
        } catch (\Throwable $th) {
            $this->error(now() .' '. $th->getMessage() . ' at ' . $th->getFile() . ':' . $th->getLine());
        }
    }

    public function get_settings(){
        $all_settings = RecipientSetting::withoutGlobalScopes()->where('type', 'daily_metrics')->get();
        foreach($all_settings as $setting){
            $recipients_ids = explode(',',$setting->recipients);
            $sms_server = SmsSetting::withoutGlobalScopes()->where('ins', $setting->ins)->first();
            $email_setting = EmailSetting::withoutGlobalScopes()->where('ins',$setting->ins)->first();
            $recipientDetails = Hrm::withoutGlobalScopes()->where('status',1)->whereIn('id', $recipients_ids)
            ->get()
            ->map(function ($user) {

                return (object) [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->personal_email,
                    'phone_number' => $user->meta ? $user->meta->secondary_contact : '',
                    'ins' => optional($user->business)->id,
                    'business' => optional($user->business)->cname,
                    'dbm_uuid' => ''
                ];

            });
            $phone_numbers = [];
            $user_ids = [];
            $emails = [];
            $dbm_uid = '';
            $pattern = '/^(07\d{8}|2547\d{8})$/';
            $data = [];
            foreach ($recipientDetails as $rD) {

                $dbmDisplayOptions = DbmDisplayOption::where('ins', $rD->ins)->first();
                
                if(!$dbmDisplayOptions) continue;
                $options = $dbmDisplayOptions->options;
    
                $dbm = new DailyBusinessMetric();
                $dbm->dbm_uuid = 'DBM-' . Str::uuid()->toString();
                $dbm->date = (new DateTime())->format('Y-m-d');
                $dbm->options = $options;
                $dbm->ins = $rD->ins;
    
                $dbm->save();


                if($rD->phone_number){
                    $cleanedNumber = preg_replace('/\D/', '', $rD->phone_number);
                    if (preg_match($pattern, $cleanedNumber)) {
                        $phone_numbers[] = $cleanedNumber;
                        $user_ids[] = $rD->id;
                        $emails[] = $rD->email;
                        $dbm_uid = $dbm->dbm_uuid;
                        $data[] = [
                            'phone_number' => $cleanedNumber,
                            'user_id' => $rD->id,
                            'dbm_uuid' => $dbm->dbm_uuid,
                            'email' =>$rD->email,
                        ];
                    }
                }
    
                $rD->dbm_uuid = $dbm->dbm_uuid;

                $email_input = [
                    'text' => '',
                    'subject' => 'The 8pm Daily Operations Summary Report',
                    'mail_to' => $rD->email,
                ];
                if($setting->email == 'yes' && $email_setting && $email_setting->active == 1){
                    $email = (new RosemailerRepository($rD->ins))->send($rD, $email_input, 'emails.dailyBusinessMetrics');
                    $email_output = json_decode($email);
                    if ($email_output->status === "Success"){

                        $email_data = [
                            'text_email' => $email_input['subject'],
                            'subject' => $email_input['subject'],
                            'user_emails' => $email_input['mail_to'],
                            'user_ids' => $rD->id,
                            'ins' => $rD->ins,
                            'user_id' => $rD->ins,
                            'status' => 'sent'
                        ];
                        SendEmail::create($email_data);
                    }
                    
                }

            }
            $contacts = implode(',', $phone_numbers);
            $users = implode(',', $user_ids);
            $company = Company::find($setting->ins);
            $company_name = Str::title($company->cname);
            $date = Carbon::now()->format('D, d M Y');
            $subject = "From {$company->sms_email_name}: The 8pm Daily Operations Summary Report for {$date}, Please click the link below to access it: ".route('daily-business-metrics', $dbm_uid);
            $cost_per_160 = 0.6;
            $totalCharacters = strlen($subject);
            $charCount = ceil($totalCharacters/160);
            $count_users = count($user_ids);
            $sms_input = [
                'subject' => $subject,
                'user_type' =>'employee',
                'delivery_type' =>'now',
                'message_type' => 'bulk',
                'phone_numbers' => $contacts,
                'sent_to_ids' => $users,
                'characters' => $charCount,
                'cost' => $cost_per_160,
                'user_count' => $count_users,
                'total_cost' => $cost_per_160*$charCount*$count_users,
                'user_id' => $setting->user_id,
                'ins' => $setting->ins,

            ];
            if($setting->sms == 'yes' && $sms_server->active == 1){
                $send_sms = new SendSms();
                $send_sms->fill($sms_input);
                $send_sms->user_id = $setting->user_id;  // Manually assign user_id
                $send_sms->ins = $setting->ins;
                $send_sms->save();
                $this->bulk_sms($sms_input['phone_numbers'], $sms_input['subject'], $send_sms, $setting->ins);
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
