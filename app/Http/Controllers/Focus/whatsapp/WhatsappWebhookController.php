<?php

namespace App\Http\Controllers\Focus\whatsapp;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use Illuminate\Http\Request;

class WhatsappWebhookController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // verification from Meta
        $business = Company::whereNotNull('whatsapp_verify_token')->first();
        if ($request->isMethod('get')) {
            $verifyToken = optional($business)->whatsapp_verify_token;
            $mode = $request->input('hub_mode');
            $token = $request->input('hub_verify_token');
            $challenge = $request->input('hub_challenge');

            if ($mode === 'subscribe' && $verifyToken === $token) {
                return response($challenge, 200);
            } else {
                return response('Forbidden', 403);
            }
        }

        // incoming messages
        if ($request->isMethod('post')) {
            $data = $request->all();
            \Log::info("whatsapp-webhook: " . json_encode($data));
            return response()->json($data);
        }
    }
}
