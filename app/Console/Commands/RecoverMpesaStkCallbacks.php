<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\mpesa_payment\StkPush;
use App\Repositories\MpesaAuthService;
use Illuminate\Support\Facades\DB;


class RecoverMpesaStkCallbacks extends Command
{
    protected $signature = 'mpesa:recover-stk';
    protected $description = 'Recover missed MPESA STK callbacks via STK Query API';

    protected Client $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client(['timeout' => 30]);
    }

    public function handle()
    {
        $payments = StkPush::where('status', 'PENDING')
            ->whereNotNull('checkout_request_id')
            ->where('created_at', '>=', now()->subHours(24))
            ->limit(50)
            ->get();

        foreach ($payments as $payment) {
            try {
                $this->recover($payment);
            } catch (\Throwable $e) {
                Log::error('MPESA STK recovery failed', [
                    'payment_id' => $payment->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        return 0;
    }

    protected function recover(StkPush $payment): void
    {
        // Use SAME ins as initiation
        $auth   = new MpesaAuthService(2);
        $config = $auth->getConfig();

        $timestamp = Carbon::now('Africa/Nairobi')->format('YmdHis');
        $password  = base64_encode($config->shortcode . $config->passkey . $timestamp);
        $token     = $auth->getAccessToken();

        $res = $this->client->post(
            rtrim($config->base_url, '/') . '/mpesa/stkpushquery/v1/query',
            [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => [
                    'BusinessShortCode' => (int) $config->shortcode,
                    'Password'          => $password,
                    'Timestamp'         => $timestamp,
                    'CheckoutRequestID' => $payment->checkout_request_id,
                ],
            ]
        );

        $body = json_decode($res->getBody()->getContents(), true);
        $this->applyResult($payment, $body);
    }

    protected function applyResult(StkPush $payment, array $res): void
    {
        // Avoid overwriting a real callback
        if ($payment->status !== 'PENDING') {
            return;
        }

        $resultCode = (string) ($res['ResultCode'] ?? '');
        $resultDesc = $res['ResultDesc'] ?? '';

        // EXACT same mapping as callback()
        $status = 'FAILED';
        if ($resultCode === '0') {
            $status = 'SUCCESS';
        } elseif (in_array($resultCode, ['1032', '2001'])) {
            $status = 'CANCELLED';
        }

        $payment->update([
            'result_code' => $resultCode,
            'result_desc' => $resultDesc,
            'status'      => $status,
            'paid_at'     => $status === 'SUCCESS'
                ? Carbon::now('Africa/Nairobi')
                : null,
            'raw_query'   => $res,
        ]);

        Log::info('MPESA STK recovered', [
            'checkout_request_id' => $payment->checkout_request_id,
            'status'              => $status,
            'res' => $res,
        ]);
    }
}
