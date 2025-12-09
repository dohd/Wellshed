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
            $mode = $request->get('hub_mode');
            $token = $request->get('hub_verify_token');
            $challenge = $request->get('hub_challenge');

            if ($mode === 'subscribe' && $verifyToken === $token) {
                return response($challenge, 200);
            } 
            
            \Log::error('Invalid verify token: ' . $token);
            return response('Invalid verify token', 403);
        }

        // incoming messages
        if ($request->isMethod('post')) {
            $data = $request->all();
            \Log::info("whatsapp webhook payload: " . json_encode($data));
            return response()->json($data);
        }
    }
}
