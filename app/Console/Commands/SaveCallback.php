<?php

namespace App\Console\Commands;

use App\Models\Company\SmsSetting;
use App\Models\sms_response\SmsCallback;
use App\Models\sms_response\SmsResponse;
use DB;
use Illuminate\Console\Command;

class SaveCallback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:callback_save';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save SMS Callback';

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
            DB::beginTransaction();
            $sms_sent_response = SmsResponse::withoutGlobalScopes()->whereDoesntHave('sms_callbacks', function ($q){
                $q->withoutGlobalScopes();
            })->get();
            foreach($sms_sent_response as $sent_response){
                $message_id = $sent_response->message_response_id;
                $response = $this->get_sms_with_no_callback($message_id, $sent_response->ins ?? 2);
                $sms_response = $response->getData(true);
                if($sms_response['status'] == true){
                    foreach ($sms_response['messages'] as $message) {
                        $phone = $message['phone'];
                        $messageText = $message['message'];
                        $cost = $message['cost'];
                        $sentTime = $message['sentTime'];
                        $deliveryStatus = $message['deliveryStatus']['status'];
                        $deliveryTime = $message['deliveryStatus']['deliveryTime'];
                        $data = [
                            'delivery_status' => $deliveryStatus,
                            'delivery_time' => $deliveryTime,
                            'reference' => $message_id,
                            'msisdn' => $phone,
                            'cost' => $cost,
                            'sender' => 'NA',
                        ];
                        SmsCallback::create($data);
                    }
                }
            }
            DB::commit();
            $this->info(now() .' Get Callback SMS: ');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $this->error(now() .' '. $th->getMessage() . ' at ' . $th->getFile() . ':' . $th->getLine());
        }
        
    }

    public function get_sms_with_no_callback($message_response_id, $ins)
    {
        $sms_server = SmsSetting::withoutGlobalScopes()->where('ins', $ins)->first();
         // Prepare the payload
         $payload = [
            'messageId' => $message_response_id,
        ];

        // Use GuzzleHTTP to send the message
        try {
            $client = new \GuzzleHttp\Client();
            $apiToken = $sms_server->username;
            $response = $client->post('https://api.mobilesasa.com/v1/dlr', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiToken,
                ],
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);

            if ($statusCode == 200 && isset($body['status']) && $body['status'] === true) {
                return response()->json([
                    'status' => $body['status'],
                    'responseCode' => $body['responseCode'],
                    'messages' => $body['messages'] ?? [],
                ]);
            } else {
                return response()->json([
                    'status' => $body['status'],
                    'responseCode' => $body['responseCode'],
                    'messages' => $body['messages'] ?? [],
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'responseCode' => 500,
                'messages' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }


    }
}
