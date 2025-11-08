<?php

namespace App\Http\Controllers\Focus\mpesa_payment;

use App\Http\Controllers\Controller;
use App\Models\mpesa_payment\StkPush;
use App\Repositories\MpesaAuthService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class StkPushsController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 30]);
    }

    /**
     * Initiate STK Push request
     */
    public function stkPush(Request $request)
    {
        $data = $request->validate([
            'amount'            => 'required|integer|min:1',
            'phone'             => 'required|string',
            'account_reference' => 'nullable|string|max:50',
            'description'       => 'nullable|string|max:100',
            'ins'               => 'nullable|integer', // 👈 dynamic institution
        ]);

        // Resolve MpesaAuthService with correct ins
        $auth   = new MpesaAuthService($data['ins'] ?? null);
        $config = $auth->getConfig();
        // return response()->json($config);

        $timestamp = Carbon::now('Africa/Nairobi')->format('YmdHis');
        $password  = base64_encode($config->shortcode . $config->passkey . $timestamp);

        $payment = StkPush::create([
            'amount'            => (int) $data['amount'],
            'phone'             => preg_replace('/\D+/', '', $data['phone']),
            'account_reference' => $data['account_reference'] ?? 'AccountRef',
            'status'            => 'PENDING',
            'ins'               => $config->ins,
        ]);

        $payload = [
            'BusinessShortCode' => (int) $config->shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => (int) $payment->amount,
            'PartyA'            => (int) $payment->phone,
            'PartyB'            => (int) $config->shortcode,
            'PhoneNumber'       => (int) $payment->phone,
            'CallBackURL'       => $config->callback_url,
            'AccountReference'  => $payment->account_reference,
            'TransactionDesc'   => $data['description'] ?? 'Payment',
        ];

        try {
            $token = $auth->getAccessToken();

            $res = $this->client->post("{$config->base_url}/mpesa/stkpush/v1/processrequest", [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => $payload,
            ]);

            $body = json_decode($res->getBody()->getContents(), true);

            if (isset($body['ResponseCode']) && (string) $body['ResponseCode'] === '0') {
                $payment->update([
                    'merchant_request_id' => $body['MerchantRequestID'] ?? null,
                    'checkout_request_id' => $body['CheckoutRequestID'] ?? null,
                    'status'              => 'PENDING',
                ]);
            } else {
                $payment->update([
                    'status'      => 'ERROR',
                    'result_code' => $body['ResponseCode'] ?? null,
                    'result_desc' => $body['ResponseDescription'] ?? 'Failed to initiate STK',
                ]);
            }

            return response()->json([
                'ok'                  => true,
                'payment_id'          => $payment->id,
                'merchant_request_id' => $payment->merchant_request_id,
                'checkout_request_id' => $payment->checkout_request_id,
                'status'              => $payment->status,
                'gateway'             => $body,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('STK Push error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $payment->update([
                'status'      => 'ERROR',
                'result_desc' => 'Exception: ' . $e->getMessage(),
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'Failed to initiate STK Push.',
            ], 500);
        }
    }

    /**
     * Callback from Safaricom
     */
    public function callback(Request $request)
    {
        $payload = $request->getContent();
        Log::info('MPESA Callback RAW: ' . $payload);

        $json = json_decode($payload, true);
        if (!$json || !isset($json['Body']['stkCallback'])) {
            return response()->json(['message' => 'Invalid callback'], 400);
        }

        $cb = $json['Body']['stkCallback'];

        $merchantRequestID = $cb['MerchantRequestID'] ?? null;
        $checkoutRequestID = $cb['CheckoutRequestID'] ?? null;
        $resultCode        = (string) ($cb['ResultCode'] ?? '');
        $resultDesc        = $cb['ResultDesc'] ?? '';

        $payment = StkPush::where('checkout_request_id', $checkoutRequestID)->first();
        if (!$payment) {
            $payment = StkPush::create([
                'merchant_request_id' => $merchantRequestID,
                'checkout_request_id' => $checkoutRequestID,
                'status'              => 'PENDING',
            ]);
        }

        $status = 'FAILED';
        if ($resultCode === '0') {
            $status = 'SUCCESS';
        } elseif (in_array($resultCode, ['1032', '2001'])) {
            $status = 'CANCELLED';
        }

        $items = $cb['CallbackMetadata']['Item'] ?? [];
        $get = function ($name) use ($items) {
            foreach ($items as $it) {
                if (($it['Name'] ?? '') === $name) {
                    return $it['Value'] ?? null;
                }
            }
            return null;
        };

        $mpesaReceipt   = $get('MpesaReceiptNumber');
        $amount         = $get('Amount');
        $phone          = $get('PhoneNumber');
        $transactionRaw = $get('TransactionDate');

        $paidAt = null;
        if ($transactionRaw) {
            $paidAt = Carbon::createFromFormat('YmdHis', (string) $transactionRaw, 'Africa/Nairobi')
                ->timezone('Africa/Nairobi');
        }

        $payment->update([
            'merchant_request_id'  => $merchantRequestID,
            'checkout_request_id'  => $checkoutRequestID,
            'result_code'          => $resultCode,
            'result_desc'          => $resultDesc,
            'mpesa_receipt_number' => $mpesaReceipt,
            'amount'               => $amount ?? $payment->amount,
            'phone'                => $phone ?? $payment->phone,
            'paid_at'              => $paidAt,
            'status'               => $status,
            'raw_callback'         => $json,
        ]);

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received successfully'], 200);
    }

    /**
     * Check payment status
     */
    public function delivery_status($checkoutRequestID)
    {
        $payment = StkPush::where('checkout_request_id', $checkoutRequestID)->first();
        if (!$payment) {
            return response()->json(['ok' => false, 'message' => 'Not found'], 404);
        }
        return response()->json(['ok' => true, 'status' => $payment->status, 'data' => $payment]);
    }
}
