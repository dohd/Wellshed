<?php

namespace App\Http\Controllers\Focus\whatsapp;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\meta_whatsapp\MetaWhatsappThread;
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
        // Meta verification
        if ($request->isMethod('get')) {
            $business = Company::whereNotNull('whatsapp_verify_token')->first();
            $verifyToken = optional($business)->whatsapp_verify_token;
            $mode = $request->get('hub_mode');
            $token = $request->get('hub_verify_token');
            $challenge = $request->get('hub_challenge');

            if ($mode === 'subscribe' && $verifyToken === $token) {
                \Log::info('whatsapp-webhook success challenge: ' . $challenge);
                return response($challenge, 200);
            } 
            
            \Log::error('Invalid verify token: ' . $token);
            return response('whatsapp-webhook invalid  verify token', 403);
        }

        // Incoming messages
        if ($request->isMethod('post')) {
            $payload = $request->all();
            \Log::info("whatsapp-webhook payload: " . json_encode($payload));

            try {
                $business = optional(Company::whereNotNull('whatsapp_verify_token')->first());
                $metadata = $payload['entry'][0]['changes'][0]['value']['metadata'] ?? [];
                $contacts = $payload['entry'][0]['changes'][0]['value']['contacts'][0] ?? [];
                $messages = $payload['entry'][0]['changes'][0]['value']['messages'][0] ?? [];
                $statuses = $payload['entry'][0]['changes'][0]['value']['statuses'][0] ?? [];

                if ($statuses) {
                    $thread = MetaWhatsappThread::make([
                        'object' => $payload['object'],
                        'entry_id' => $payload['entry'][0]['id'],
                        'display_phone_number' => $metadata['display_phone_number'],
                        'phone_number_id' => $metadata['phone_number_id'],
                        'status' => $statuses['status'],
                        'timestamp' => $statuses['timestamp'],
                        'recipient_id' => $statuses['recipient_id'],
                        'billable' => $statuses['pricing']['billable'],
                        'pricing_model' => $statuses['pricing']['pricing_model'],
                        'pricing_category' => $statuses['pricing']['category'],
                        'pricing_type' => $statuses['pricing']['type'],
                        'ins' => $business->id,
                    ]);
                } else {
                    $thread = MetaWhatsappThread::make([
                        'object' => $payload['object'],
                        'entry_id' => $payload['entry'][0]['id'],
                        'display_phone_number' => $metadata['display_phone_number'],
                        'phone_number_id' => $metadata['phone_number_id'],
                        'contact_name' => $contacts['profile']['name'],
                        'wa_id' => $contacts['wa_id'],
                        'message_id' => $messages['id'],
                        'from' => $messages['from'],
                        'timestamp' => $messages['timestamp'],
                        'type' => $messages['type'],
                        'message_body' => $messages['text']['body'],
                        'ins' => $business->id,
                    ]);
                }

                $thread->save();

                return response()->json($thread);
            } catch (\Exception $e) {
                \Log::error("whatsapp-webhook payload error: " . $e->getMessage() . ' on ' . $e->getFile() . ':' . $e->getLine());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error prococessing whatsapp-webhook payload: ' . $e->getMessage(),
                ], 500);
            }
        }
    }
}
