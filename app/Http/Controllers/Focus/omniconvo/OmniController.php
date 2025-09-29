<?php

namespace App\Http\Controllers\Focus\omniconvo;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\lead\AgentLead;
use App\Models\lead\OmniChat;
use App\Models\lead\OmniFeedback;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Log;

class OmniController extends Controller
{
    /**
     * Create and store chats
     */
    public function handle(Request $request, Company $company)
    {
        $data = $request->all();

        $fbId = @$data['fbId'];
        $botId = @$data['bot_id'];
        $username = @$data['username'];
        $botname = @$data['botname'];
        $date = @$data['date'];

        DB::beginTransaction();
        // Recreate Chat
        if ($fbId) {
            $chat = OmniChat::firstOrCreate(
                ['fb_id' => $fbId],
                [
                    'username' => $username,
                    'botname' => $botname,
                    'ins' => $company['id'],
                ]
            );

            // if botkey is set, update
            if (@$data['bot_key']) {
                $url = @$data['url'];
                $chat->update([
                    'bot_key' => @$data['bot_key'],
                    'url' => $url,
                ]);
                $hasMessage = $chat->messages()->where(['message' => @$data['text']])->exists();
                if (!$hasMessage) {
                    $chat->messages()->create([
                        'message' => @$data['text'],
                        'date' => $date,
                        'ins' => $company['id'],
                    ]);
                }
            } else {
                // initial message
                $chat->messages()->create([
                    'username' => $username,
                    'message' => @$data['message'],
                    'date' => $date,
                    'ins' => $company['id'],
                ]);
            }
        }

        // Bot Message
        if (@$data['user_id']) {
            $chat = OmniChat::where('fb_id', $data['user_id'])->first();
            if ($chat && $botId) {
                $messageData = [
                    'bot_id' => $data['bot_id'],
                    'payload_id' => @$data['id'],
                    'username' => @$data['user_name'],
                    'message' => @$data['message']['text'],
                    'date' => $date,
                    'ins' => $company['id'],
                ];
                // Admin assit to Bot
                if (@$data['from_user_id']) {
                    $chat->messages()->create(array_replace($messageData, [
                        'from_user_id' => $data['from_user_id'],
                        'from' => @$data['from'],
                    ]));
                }
                // Fully Bot
                else {
                    $chat->messages()->create($messageData);
                }
            }
        }

        // Set temp username for initial convo starter where no username was assigned
        if (@$chat && @$chat->fb_id) {
            if (!$chat->username) {
                $chat->update(['username' => $chat->fb_id]);
                $chat->messages()->whereNull('username')->update(['username' => $chat->fb_id]);
            } elseif ($chat->fb_id == $chat->username) {
                $username = (@$data['username'] ?: @$data['user_name']);
                if ($username) {
                    $chat->update(['username' => $username]);
                    $chat->messages()->whereNull('username')->update(['username' => $username]);
                }
            }
        }

        DB::commit();

        if (!@$chat) {
            $message = 'Chat not found, fbId: ' . ($fbId ?: @$data['user_id']);
            Log::error($message);
            return response()->json(['status' => 'error', 'message' => $message], 400);
        }

        $chat = OmniChat::where('id', $chat->id)->with('messages')->first();
        return response()->json(['status' => 'success', 'data' => $chat], 200);
    }

    /**
     * Fetch Chat Transcripts
     */
    public function getTranscript(Request $request)
    {
        // validate request
        $company = Company::find($request->ins);
        if ($request->header('X-Signature') !== $company->omniconvo_key) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $chat = OmniChat::where('fb_id', $request->fb_id)->whereHas('messages')->with('messages')->first();
        if (!$chat) return response()->json(['error' => 'Chat not found'], 404);

        // Format the transcript
        $lastReadMsg = $chat->messages->where('is_read', 1)->sortByDesc('id')->first();
        $transcript = $chat->messages->map(function ($message) use ($lastReadMsg) {
            return [
                'id' => $message->id,
                'timestamp' => date('Y-m-d H:i:s', strtotime($message->date)),
                'sender' => $message->bot_id ? 'Agent' : $message->username,
                'message' => (string) $message->message,
                'bot_id' => (string) $message->bot_id,
                'last_read_id' => (string) @$lastReadMsg->id,
            ];
        });

        return response()->json([
            'fb_id' => $chat->fb_id,
            'channel' => $chat->botname,
            'transcript' => $transcript,
        ]);
    }

    /**
     * Create and store form feedback
     */
    public function formFeedback(Request $request, Company $company)
    {
        $request->validate([
            'Name' => ['nullable', 'present'],
            'Phone_No' => 'required_without:Email',
            'Email' => 'required_without:Phone_No',
            // 'Job_Definition' => 'required',
        ]);
        $data = $request->all();

        DB::beginTransaction();
        $feedback = OmniFeedback::create([
            'username' => $data["{user_name}"],
            'fb_id' => $data["{fbId}"],
            'user_id' => $data["{user_id}"],
            'bot_id' => $data["{bot_id}"],
            'bot_name' => $data["{bot_name}"],
            'form_name' => $data["{form_name}"],
            'raw' => $data['raw'],
            'submitted_at' => $data["submitted_at"],
            'ins' => $company['id'],
        ]);

        $chat = OmniChat::where('fb_id', $feedback->fb_id)->first(['id']);
        if ($chat) $feedback->update(['omni_chat_id' => $chat->id]);

        $lead = AgentLead::create([
            'client_name' => $data['Name'],
            'phone_no' => @$data['Phone_No'],
            'email' => @$data['Email'],
            'project' => @$data['Job_Definition'],
            'location' => @$data['Location'],
            'product_brand' => @$data['Product_Brand'],
            'product_spec' => @$data['Product_Specification'],
            'ins' => $company['id'],
        ]);
        if ($lead) $feedback->update(['agent_lead_id' => $lead->id]);
        DB::commit();

        return response()->json(['status' => 'Success', 'data' => ['feedbackId' => $feedback->id]]);
    }

    /**
     * Fetch List of Chats
     */
    public function queryChats()
    {
        $business = Company::find(request('ins'));
        $chats = OmniChat::query()
            ->where('ins', @$business->id)
            ->whereHas('messages', function ($q) {
                $q->when(request('start_date') && request('end_date'), function ($q) {
                    $q->whereDate('date', '>=', date_for_database(request('start_date')));
                    $q->whereDate('date', '<=', date_for_database(request('end_date')));
                });
            })
            ->when(request('user_type'), fn($q) => $q->where('user_type', request('user_type')))
            ->with('messages')
            ->get()
            ->map(function ($chat) use ($business) {
                $lastMsg = $chat->messages[$chat->messages->count() - 1];
                $unreadCount = $chat->messages->whereNull('is_read')->count();
                return [
                    'id' => $chat->id,
                    'fb_id' => $chat->fb_id,
                    'username' => $chat->username,
                    'url' => $chat->url,
                    'phone_no' => (string) $chat->phone_no,
                    'country' => (string) $chat->country,
                    'user_type' => (string) $chat->user_type,
                    'last_message_id' => $lastMsg->id,
                    'last_message' => (string) $lastMsg->message,
                    'last_timestamp' => (string) $lastMsg->date,
                    'last_date' => $lastMsg->date ? dateFormat($lastMsg->date, 'Y-m-d') : '',
                    'unread_count' => $unreadCount,
                ];
            })
            ->sortByDesc('last_timestamp')
            ->values()
            ->all();

        return response()->json($chats);
    }

    /**
     * Mark chart as read
     */
    public function readChat()
    {
        $chat = OmniChat::where('ins', request('ins'))
            ->where('fb_id', request('fb_id'))
            ->whereHas('messages')
            ->first();
        if (!$chat) return response()->json(['status' => 'Error', 'message' => 'Chat not found, fb_id: ' . request('fb_id')], 500);

        // mark chart messages as read
        $chat->messages()
            ->where('id', '<=', request('last_message_id'))
            ->whereNull('is_read')
            ->update(['is_read' => 1]);

        return response()->json(['status' => 'Success']);
    }

    /**
     * Fetch Access Token
     */
    public function fetchAccessToken()
    {
        try {
            $baseUrl = config('services.omniconvo.base_url');
            $business = auth()->user()->business;
            // credentials
            $email = @$business->omniUser->email;
            $password = @$business->omniUser->password;
            if ($email && $password) {
                $password = base64_decode($password);
            }

            // Login for client_secret
            $client = new \GuzzleHttp\Client(['timeout' => 15]);
            $promise = $client->postAsync($baseUrl . '/api/v1/login', [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Accept' => "application/json",
                ],
                'json' => [
                    'email' => $email,
                    'password' => $password,
                ],
            ]);

            $promise->then(
                function ($response) use ($email, $password, $client, $baseUrl) {
                    $loginData = json_decode($response->getBody()->getContents());
                    if (@$loginData->client_secret) Log::info('Omniconvo, client secret set');

                    // Fetch Access Token using client_secret
                    return $client->postAsync($baseUrl . '/oauth/token', [
                        'headers' => [
                            'Content-Type' => "application/json",
                            'Accept' => "application/json",
                        ],
                        'json' => [
                            'grant_type' => 'password',
                            'client_id' => (string) @$loginData->client_id,
                            'client_secret' => (string) @$loginData->client_secret,
                            'username' => $email,
                            'password' => $password,
                        ],
                    ]);
                },
                function ($e) {
                    Log::error('Omniconvo, login error: ' . $e->getMessage());
                }
            )
            ->then(
                function ($response) {
                    $tokenData = json_decode($response->getBody()->getContents());
                    $accessToken = @$tokenData->access_token;
                    if ($accessToken) Log::info('Omniconvo, access token set');

                    session(['omniconvoAccessToken' => $accessToken]);
                    return $accessToken;
                },
                function ($e) {
                    Log::error('Omniconvo, fetch access token error: ' . $e->getMessage());
                }
            )
            ->otherwise(
                function ($e) {
                    Log::error('Omniconvo, error: ' . $e->getMessage());
                }
            );

            $promise->wait();
        } catch (Exception $e) {
            Log::error($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    /**
     * Send Message to User (Human-bot intervention)
     */
    public function sendUserMessage(Request $request)
    {
        $request->validate([
            'fb_id' => 'required',
            'user_type' => 'required',
            'message' => 'required',
        ]);
        $input = $request->only('fb_id', 'user_type', 'message');

        try {
            $token = session('omniconvoAccessToken');
            if (!$token) return response()->json(['status' => 'Error', 'message' => 'Unauthorized'], 401);

            $baseUrl = config('services.omniconvo.base_url');
            $business = auth()->user()->business;
            $apiKey = $business->omniUser->api_key;
            $fromUser = $business->omniUser->name;
            $reqBody = [
                'apikey' => $apiKey,
                'to' => $input['fb_id'],
                'type' => $input['user_type'], //whatsapp , facebook, website
                'message' => [
                    [
                        'text' => $input['message']
                    ],
                ],
                'stop_bot_for_user' => false,
                'from' => $fromUser,
            ];

            // Post message
            $client = new \GuzzleHttp\Client(['timeout' => 15]); // timeout 15sec
            $response = $client->post($baseUrl . '/api/v1/converse/send-message-to-user/json', [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Accept' => "application/json",
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => $reqBody,
            ]);
            $messageData = json_decode($response->getBody()->getContents());
            if (@$messageData->status != 'success') {
                Log::info('User-message Error: ', (array) $messageData);
                return response()->json(['status' => 'Error', 'message' => 'Error processing message!'], 403);
            }
            return response()->json(['status' => 'Success', 'message' => 'Message posted successfully!']);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'Error', 'message' => 'Oops! Something went wrong, try again later'], 500);
        }
    }

    /**
     * Fetch List of Bot Users
     */
    public function queryBotUsers($data = [])
    {
        try {
            $baseUrl = config('services.omniconvo.base_url');
            $accessToken = Cache::get('omniconvoAccessToken');

            $client = new \GuzzleHttp\Client(['timeout' => 15]);
            $promise = $client->postAsync($baseUrl . '/api/v1/get-bot-users', [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Accept' => "application/json",
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'json' => [
                    'email' => $data['email'],
                    'apikey' => $data['apiKey'],
                    'from_date' => '2025-02-01', // implementation date
                    'skip' => 0,
                    "fbId_" => "123456", // "fbId": filter by fbId
                    'limit_' => 0, // "limit": set limit 
                ],
            ]);

            $userData = [];
            $promise->then(
                function ($response) use (&$userData) {
                    $userData = json_decode($response->getBody()->getContents());
                    if (@$userData->status != 'success') {
                        Log::info('Omniconvo, query bot-users error response: ', (array) $userData);
                        return false;
                    }
                    $userData = $userData->messengerUsers;
                    return $userData;
                },
                function ($e) {
                    Log::error('Omniconvo, query bot-users error: ' . $e->getMessage());
                    return false;
                }
            );
            $promise->wait();

            return $userData;
        } catch (Exception $e) {
            Log::error($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return false;
        }
    }

    /**
     * Whatsapp Broadcast Create Page
     */
    public function whatsappBroadcastCreate()
    {
        $contacts = OmniChat::where('user_type', 'whatsapp')
        ->whereNotNull('phone_no')
        ->pluck('username', 'phone_no');

        return view('focus.broadcasts.create', compact('contacts'));
    }


    /**
     * Whatsapp Broadcast Index Page
     */
    public function whatsappBroadcastIndex()
    {
        return view('focus.broadcasts.index');
    }

    /** 
     * Download User Template for Broadcast
     * */
    public function downloadUserTemplate()
    {
        $filepath = Storage::disk('public')->path('sample/whatsapp-users.csv');
        if (!is_file($filepath)) abort(404, 'File not found.');

        return Storage::disk('public')->download('sample/whatsapp-users.csv');
    }

    /** 
     * Media Blocks Index Page
     * */
    public function mediaBlocksIndex()
    {
        return view('focus.media_blocks.index');
    }

    /** 
     * Media Blocks Create Page
     * */
    public function mediaBlocksCreate()
    {
        return view('focus.media_blocks.create');
    }
}
