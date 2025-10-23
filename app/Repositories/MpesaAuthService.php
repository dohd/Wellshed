<?php

namespace App\Repositories;

use App\Models\mpesa\MpesaConfig;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MpesaAuthService
{
    protected $client;
    protected $config;

    /**
     * @param int|null $ins Institution ID
     */
    public function __construct(?int $ins = null)
    {
        $this->client = new Client(['timeout' => 30]);

        // Resolve ins: priority -> argument -> auth()->user()->ins -> null
        $ins = $ins ?? (auth()->check() ? auth()->user()->ins : null);

        // Load MpesaConfig for this ins
        $this->config = MpesaConfig::where('ins', $ins)
            ->where('type','stk_push')
            ->firstOrFail(); // will throw if no config
    }

    /**
     * Get OAuth access token for Mpesa API
     */
    public function getAccessToken(): string
    {
        $cacheKey = "mpesa_access_token_{$this->config->ins}";

        return Cache::remember($cacheKey, now()->addMinutes(50), function () {
            try {
                $credentials = base64_encode(
                    $this->config->consumer_key . ':' . $this->config->consumer_secret
                );

                $res = $this->client->get(
                    rtrim($this->config->base_url, '/') . '/oauth/v1/generate?grant_type=client_credentials',
                    [
                        'headers' => [
                            'Accept'        => 'application/json',
                            'Authorization' => 'Basic ' . $credentials,
                        ],
                    ]
                );

                $body = json_decode($res->getBody()->getContents(), true);

                if (!isset($body['access_token'])) {
                    Log::error('Mpesa OAuth: missing access_token', ['body' => $body]);
                    throw new \RuntimeException('Mpesa OAuth failed: access_token not found');
                }

                return $body['access_token'];
            } catch (\Throwable $e) {
                Log::error('Mpesa OAuth error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Force refresh OAuth token (clear cache)
     */
    public function refresh(): string
    {
        $cacheKey = "mpesa_access_token_{$this->config->ins}";
        Cache::forget($cacheKey);
        return $this->getAccessToken();
    }

    /**
     * Get active MpesaConfig
     */
    public function getConfig(): MpesaConfig
    {
        return $this->config;
    }
}
