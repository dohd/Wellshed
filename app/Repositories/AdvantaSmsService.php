<?php

namespace App\Repositories;

use App\Models\sms_log\SmsLog;
use GuzzleHttp\Client;
use Exception;

class AdvantaSmsService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('advantasms.endpoint'),
            'timeout'  => 10,
        ]);
    }

    public function send($mobile, $message)
    {
        // Save initial log
        $log = SmsLog::create([
            'mobile'  => $mobile,
            'message' => $message,
            'status'  => 'pending',
        ]);

        try {
            $response = $this->client->post('', [
                'form_params' => [
                    'apikey'     => config('advantasms.apikey'),
                    'partnerID'  => config('advantasms.partner_id'),
                    'shortcode'  => config('advantasms.shortcode'),
                    'mobile'     => $mobile,
                    'message'    => $message,
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            // Extract message_id & status (depends on Advanta response format)
            $messageId = $result['responses'][0]['messageid'] ?? null;
            $status = $result['responses'][0]['status'] ?? 'sent';

            // Update log
            $log->update([
                'message_id' => $messageId,
                'status'     => $status,
                'response'   => json_encode($result),
            ]);

            return $result;

        } catch (Exception $e) {
            $log->update([
                'status'   => 'failed',
                'response' => $e->getMessage(),
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    public function checkDeliveryStatus($messageId)
    {
        try {
            $response = $this->client->post('deliveryreport', [
                'form_params' => [
                    'apikey'     => config('advantasms.apikey'),
                    'partnerID'  => config('advantasms.partner_id'),
                    'messageID'  => $messageId,
                ],
            ]);

            $result = json_decode($response->getBody(), true);
            return $result;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

}
