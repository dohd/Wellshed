<?php

namespace App\Console\Commands;

use App\Models\Company\Company;
use App\Models\Company\SmsSetting;
use App\Models\send_sms\SendSms;
use App\Models\sms_response\SmsResponse;
use App\Repositories\Focus\general\RosesmsRepository;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;

class SendScheduledSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:scheduled-sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending Scheduled SMS';

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
        $sms_list = SendSms::withoutGlobalScopes()->where('delivery_type', 'schedule')
        ->where('scheduled_date', '<=', now())
        ->doesntHave('sms_response')
        ->get();
        // dd($sms_list, now()->toDateString());
        foreach ($sms_list as $list)
        {
            $company = Company::find($list->ins);
            
            $this->bulk_sms($list['phone_numbers'], $list['subject'], $list, $company->id);
        }
        $this->info(now() .' Scheduled SMS Sent: ');
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
