<?php

namespace App\Repositories\Focus\general;

use App\Models\Company\ConfigMeta;
use App\Models\Company\EmailSetting;
use App\Models\Company\SmsSetting;
use DB;
use App\Exceptions\GeneralException;
use App\Models\sms_response\SmsResponse;
use App\Repositories\BaseRepository;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Twilio\Exceptions\RestException;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\RequestException;

/**
 * Class HrmRepository.
 */
class RosesmsRepository extends BaseRepository
{

    private $sms_server;
    private $sms_sender;
    private $sms_username;
    private $sms_password;
    private $driver;
    public $ins;

    public function __construct($ins)
    {
        $this->sms_server = SmsSetting::withoutGlobalScopes()->where('ins', $ins)->first();
        if (!$this->sms_server->active) {
            $this->driver = 1;
            $this->sms_username = getenv('TWILLIO_SID');
            $this->sms_password = getenv('TWILLIO_PASSWORD');
            $this->sms_sender = getenv('TWILLIO_TOKEN');
            $this->ins = $ins;
        } else {
            $this->driver = $this->sms_server->driver_id;
            $this->sms_username = $this->sms_server->username;
            $this->sms_password = $this->sms_server->password;
            $this->sms_sender = $this->sms_server->sender;
            $this->ins = $ins;
        }

    }


    public function send_sms($mobile, $text_message, $flag = true)
    {
        $text_message = strip_tags(  $text_message);
        $mobile = strip_tags( $mobile);
        switch ($this->driver) {
            case 1:
                $result = $this->twilio($mobile, $text_message);
                break;
            case 2:
                $result = $this->textlocal($mobile, $text_message);
                break;
            case 3:
                $result = $this->clockwork($mobile, $text_message);
                break;
            case 4:
                $result = $this->msg91($mobile, $text_message);
                break;
            case 5:
                $result = $this->bulk_sms($mobile, $text_message);
                break;
            case 6:
                $result = $this->nexmo($mobile, $text_message);
                break;
            case 7:
                $result = $this->generic($mobile, $text_message);
                break;
        }

        if($flag) return $result;
    }


    private function twilio($mobile, $text_message)
    {
        // A Twilio phone number you purchased at twilio.com/console
        // the body of the text message you'd like to send
        try {
            $client = new \Twilio\Rest\Client($this->sms_username, $this->sms_password);

            // the number you'd like to send the message to
            $message = $client->messages->create(
                $mobile,
                array(
                    'from' => $this->sms_sender,
                    'body' => $text_message
                )
            );
        }
        catch (RestException $e ) {
         return array('status' => 'Error', 'message' => 'CODE '.$e->getCode().' '.$e->getMessage());
        }

        if (@$message->sid) {
            return array('status' => 'Success', 'message' => $message->status);
        } else {
            return array('status' => 'Error', 'message' => trans('general.sms_error'));
        }
    }

    public function textlocal($mobile, $text_message, $send_sms_id)
    {
         // Prepare the payload
         $payload = [
            'senderID' => $this->sms_sender,
            'message' => $text_message,
            'phone' => $mobile,
        ];

        // Use GuzzleHTTP to send the message
        try {
            $client = new \GuzzleHttp\Client();
            $apiToken = $this->sms_username;
            $response = $client->post('https://api.mobilesasa.com/v1/send/message', [
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
                $data = [
                    'send_sms_id' => $send_sms_id->id,
                    'message_response_id' => $body['messageId'],
                    'status' => $body['status'],
                    'message_type' => 'single',
                    'phone_number_count' => 1,
                    'response_code' => $body['responseCode'],
                ];
                $sms_res = SmsResponse::create($data);
                return response()->json([
                    'status' => 'success',
                    'message' => $body['message'] ?? 'Message sent successfully!',
                    'messageId' => $body['messageId'] ?? null,
                    'callback' => $data,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to send the message.',
                    'details' => $body,
                ], $statusCode);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }


    }


    private function clockwork($mobile, $text_message)
    {
        $apiKey = urlencode($this->sms_username);
        // Message details

        $sender = urlencode($this->sms_sender);
        $text_message = rawurlencode($text_message);

        // Prepare data for POST request
        $data = array('key' => $apiKey, 'to' => $mobile, "sender" => $sender, "content" => $text_message);
        // Send the POST request with cURL
        $ch = curl_init('https://api.clockworksms.com/http/send.aspx');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Process your response here
        if (@$response) {
            return array('status' => 'Success', 'message' => $response);
        } else {
            return array('status' => 'Error', 'message' => trans('general.sms_error'));
        }
    }


    private function msg91($mobile, $text_message)
    {
        $country = 91;
        $sender_id =  $this->sms_sender;
        $route = '4';
        $authkey = $this->sms_username;
        $text_message = urlencode($text_message);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.msg91.com/api/sendhttp.php?route=4&sender=$sender_id&message=$text_message&country=91&mobiles=$mobile&authkey=$authkey",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return array('status' => 'Error', 'message' => $err);
        }

        if (@$response) {
            return array('status' => 'Success', 'message' => 'Response '.$response);
        } else {
            return array('status' => 'Error', 'message' => trans('general.sms_error'));
        }

    }

    public function bulk_sms($mobile, $text_message, $send_sms_id)
    {
        // Prepare the payload
        $payload = [
            'senderID' => $this->sms_sender,
            'message' => $text_message,
            'phones' => $mobile,
        ];

        // Use GuzzleHTTP to send the message
        try {
            $client = new \GuzzleHttp\Client();
            $apiToken = $this->sms_username;
            
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

            if (isset($content['bulkId']) && isset($content['status'])) {
                
                $phone_numbers = explode(',', $mobile);
                $data = [
                    'send_sms_id' => $send_sms_id->id,
                    'message_response_id' => $content['bulkId'],
                    'status' => $content['status'],
                    'message_type' => 'bulk',
                    'phone_number_count' => count($phone_numbers),
                    'response_code' => $content['responseCode'] ?? null,
                ];
                

                // Save the SMS response
                SmsResponse::create($data);

                // Log the response
                return json_encode($content);
            } else {
                return json_encode($content);
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
    public function bulk_personalised_sms($messageBody, $send_sms_id)
    {
        // Prepare the payload
        $payload = [
            'senderID' => $this->sms_sender,
            'messageBody' => $messageBody,
        ];

        // Use GuzzleHTTP to send the message
        try {
            $client = new \GuzzleHttp\Client();
            $apiToken = $this->sms_username;
            
            // Make the async request to the API
            $promise = $client->postAsync('https://api.mobilesasa.com/v1/send/bulk-personalized', [
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

            if (isset($content['bulkId']) && isset($content['status'])) {
                
                $data = [
                    'send_sms_id' => $send_sms_id->id,
                    'message_response_id' => $content['bulkId'],
                    'status' => $content['status'],
                    'message_type' => 'bulk',
                    'phone_number_count' => count($messageBody),
                    'response_code' => $content['responseCode'] ?? null,
                ];
                

                // Save the SMS response
                SmsResponse::create($data);

                // Log the response
                return json_encode($content);
            } else {
                return json_encode($content);
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

    public function callback_message($message_id){
          // Prepare the payload
          $payload = [
            'messageId' => $message_id
        ];

        // Use GuzzleHTTP to send the message
        try {
            $client = new \GuzzleHttp\Client();
            $apiToken = $this->sms_username;
            
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
                printlog($body);
                return response()->json([
                    'status' => 'success',
                    'message' => $body['messages'] ?? 'Message sent successfully!',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to send the message.',
                    'details' => $body,
                ], $statusCode);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function nexmo($mobile, $text_message, $flag)
    {

    }

    private function generic($mobile, $text_message)
    {

        $config_string = explode(",", $this->sms_server->username);
        $sms_config = array();

        foreach ($config_string as $row) {
            $sub_array = explode("=", $row);
            $sms_config[$sub_array[0]] = $sub_array[1];
        }


        $sendto_field_name = @$sms_config['send_field_name'];
        $message_field_name = @$sms_config['message_field_name'];
        $method = @$sms_config['METHOD'];
        $url = @$sms_config['URL'];

        unset($sms_config['send_field_name']);
        unset($sms_config['message_field_name']);
        unset($sms_config['METHOD']);
        unset($sms_config['URL']);

        $sms_config[$sendto_field_name] = $mobile;
        $sms_config[$message_field_name] = $text_message;

        if ($method == 'POST') {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $sms_config);
        } else {
            $query_string = http_build_query($sms_config);
            $ch = curl_init($url . '?' . $query_string);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $result = json_encode($response);
        if($err)   return array('status' => 'Error', 'message' => $err);
       return array('status' => 'Success', 'message' => $result);


    }


}
