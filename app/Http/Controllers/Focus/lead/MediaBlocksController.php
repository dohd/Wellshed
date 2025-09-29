<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\lead;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\lead\Lead;
use App\Models\lead\MediaBlock;
use App\Models\lead\OmniChat;
use Exception;
use Log;
use Validator;

class MediaBlocksController extends Controller
{
    protected $ins;
    protected $apikey;
    protected $reqtoken;

    public function __construct(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !(strpos($authHeader, 'Bearer ') === 0)) {
            abort(401, 'Unauthorized: Missing or invalid Authorization header');
        }

        try {
            // Extract token
            $token = substr($authHeader, 7); // Removes "Bearer "
            $token = base64_decode($token);
            $credentials = explode(':', $token);
            $agentKey = $credentials[0]; // uuid
            $email = $credentials[1]; // email
            $company = Company::where('agent_key', $agentKey)->where('email', $email)->first();

            $this->ins = $company->id;
            $this->apikey = $company->omniconvo_key;
        } catch (\Exception $e) {
            $this->logError($e);
            abort(400, 'Server Error: Invalid Authorization header');
        }
    }

    public function logError($e)
    {
        return Log::error($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        try {
            $url = config('services.omniconvo.base_url') . '/api/v1/media-block/fetch';
            $token = config('services.omniconvo.token');
            $apikey = $this->apikey;

            // Fetch
            $client = new \GuzzleHttp\Client();
            $promise = $client->getAsync($url, [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Accept' => "application/json",
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => [
                    'apikey' => $apikey,
                ],
            ]);
            
            $successData = [];
            $promise->then(
                function ($response) use(&$successData){
                    $successData = json_decode($response->getBody()->getContents());
                },
                function (\Exception $e) {
                    throw $e;
                }
            );
            $promise->wait();

            return response()->json($successData);
        } catch (\Exception $e) {
            $this->logError($e);
            return response()->json(['status' => 'Error', 'message' => 'Error Fetching Media Block'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'text' => 'required'
        ]);
        if (!$validator->passes()) {
            return response()->json([
                'status' => 'Error', 
                'message' => 'Invalid Payload', 
                'error_message' => $validator->errors()->all(),
            ], 500);
        }
        $input = $request->only(['name', 'text']);
        $input['name'] = trim($input['name']);

        try {
            $url = config('services.omniconvo.base_url') . '/api/v1/media-block/create';
            $token = config('services.omniconvo.token');
            $apikey = $this->apikey;

            // check duplicate
            $mediaBlockExists = MediaBlock::withoutGlobalScopes()
            ->where('name', 'LIKE', '%'. $input['name'] .'%')
            ->exists();
            if ($mediaBlockExists) {
                return response()->json([
                    'status' => 'Error', 
                    'message' => 'Media block name is already taken',
                ], 409);
            }

            // create remotely
            $client = new \GuzzleHttp\Client();
            $promise = $client->postAsync($url, [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Accept' => "application/json",
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => [
                    'name' => $input['name'],
                    'apikey' => $apikey,
                    'text' => $input['text'],
                    'image_url' => null,
                    'video_url' => null,
                    'buttons' => [],
                    'language' => 'en',
                    'template_specs' => [
                        'category' => 'UTILITY',
                        'header' => 'text',
                        'button_type' => null,
                        'bodyIncludes' => [
                            'body'
                        ],
                        'variables' => [
                            'body' => []
                        ]
                    ]
                ],
            ]);
            
            $successData = [];
            $promise->then(
                function ($response) use(&$successData, $input){
                    $successData = json_decode($response->getBody()->getContents());

                    // create locally
                    $input['ins'] = $this->ins;
                    $mediaBlock = MediaBlock::create($input);

                },
                function (\Exception $e) {
                    throw $e;
                }
            );
            $promise->wait();

            return response()->json(['status' => 'Success', 'message' => 'Media Block Created Successfully']);
        } catch (\Exception $e) {
            $this->logError($e);
            return response()->json(['status' => 'Error', 'message' => 'Error Creating Media Block'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\lead\Lead $lead
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'template_id' => 'required',
        ]);
        if (!$validator->passes()) {
            return response()->json([
                'status' => 'Error', 
                'message' => 'Invalid Payload', 
                'error_message' => $validator->errors()->all(),
            ], 500);
        }

        $input = $request->only('template_id');

        try {
            $url = config('services.omniconvo.base_url') . '/api/v1/media-block/delete';
            $token = config('services.omniconvo.token');
            $apikey = $this->apikey;

            $client = new \GuzzleHttp\Client();
            $promise = $client->postAsync($url, [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Accept' => "application/json",
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => [
                    'apikey' => $apikey,
                    'payload' => 'WHATSAPP_TEMPLATE_ID-' . strval($input['template_id']),
                ],
            ]);
            
            $successData = [];
            $promise->then(
                function ($response) use(&$successData){
                    $successData = json_decode($response->getBody()->getContents());
                },
                function (\Exception $e) {
                    throw $e;
                }
            );
            $promise->wait();

            return response()->json(['statuts' => 'Success', 'message' => 'Media Block Deleted Successfully']);
        } catch (\Exception $e) {
            $this->logError($e);
            return response()->json(['status' => 'Error', 'message' => 'Error Deleting Media Block'], 500);
        }
    }

    /**
     * Show the view for the specific resource
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param \App\Models\lead\Lead $lead
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required',
        ]);
        if (!$validator->passes()) {
            return response()->json([
                'status' => 'Error', 
                'message' => 'Invalid Payload', 
                'error_message' => $validator->errors()->all(),
            ], 500);
        }

        try {
            $url = config('services.omniconvo.base_url') . '/api/v1/media-block/fetch';
            $token = config('services.omniconvo.token');
            $apikey = $this->apikey;
            $content = [
                'apikey' => $apikey,
                'template_id' => request('template_id'),
            ];

            // Fetch
            $client = new \GuzzleHttp\Client();
            $promise = $client->getAsync($url, [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Accept' => "application/json",
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => $content,
            ]);
            
            $successData = [];
            $promise->then(
                function ($response) use(&$successData){
                    $successData = json_decode($response->getBody()->getContents());
                    if (@$successData->templates && is_iterable($successData->templates)) {
                        $successData = $successData->templates[0];
                    }
                },
                function (\Exception $e) {
                    throw $e;
                }
            );
            $promise->wait();

            return response()->json($successData);
        } catch (\Exception $e) {
            $this->logError($e);
            return response()->json(['status' => 'Error', 'message' => 'Error Fetching Media Block'], 500);
        }
    }

    /** 
     * Broadcast Whatsapp Messages
     * */
    public function whatsappBroadcastStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required',
            'user_segment' => 'required',
            'phone_no' => 'nullable|required_if:user_segment,local',
            'csv_file' => 'nullable|required_if:user_segment,import|file|mimes:csv,txt',
        ]);
        if (!$validator->passes()) {
            return response()->json([
                'status' => 'Error', 
                'message' => 'Invalid Payload', 
                'error_message' => $validator->errors()->all(),
            ], 500);
        }

        $input = $request->only('template_id', 'user_segment', 'phone_no', 'import_file');

        try {
            // set phone numbers
            $phoneNos = [];
            if ($input['user_segment'] == 'local') {
                $phoneNos = array_map(fn($v) => ['phone_number' => (string) $v], $input['phone_no']);
            } else if ($input['user_segment'] == 'import') {
                $file = $request->file('csv_file');
                // Open the file for reading
                if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                    $header = fgetcsv($handle); // Read the first row (header)
                    $i = 1;
                    while (($row = fgetcsv($handle)) !== false) {
                        $i++;
                        if (isset($row[1])) $phoneNos[] = ["phone_number" => $row[1]];
                        else throw new Exception('Phone number required on line ' . strval($i));                        
                    }
                    fclose($handle);
                }
            }

            $url = config('services.omniconvo.base_url') . '/api/v1/whatsapp/single-message-send';
            $token = config('services.omniconvo.token');
            $content = [
                "apikey" => $this->apikey,
                "type" => 8,
                "template" => $input['template_id'], // e.g "WHATSAPP_TEMPLATE_ID-3343",
                "users" => $phoneNos,
                "template_data" => [
                    "category" => "AUTO_REPLY",
                    "type" => "text",
                    "Attachment_link" => "",
                    "bodyIncludes" => [
                        "body",
                        "footer",
                        "buttons",
                        "header"
                    ],
                    "variables" => [
                        "header" => null,
                        "body" => [],
                        "button" => null
                    ]
                ]
            ];

            $client = new \GuzzleHttp\Client();
            $promise = $client->postAsync($url, [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Accept' => "application/json",
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => $content,
            ]);

            $successData = [];
            $promise->then(
                function ($response) use(&$successData) {
                    $successData = json_decode($response->getBody()->getContents());
                },
                function (\Exception $e) {
                    throw $e;
                }
            );
            $promise->wait();

            return response()->json(['status' => 'Success', 'message' => 'Message Posted Successfully']);
        } catch (\Exception $e) {
            $this->logError($e);
            return response()->json(['status' => 'Error', 'message' => 'Error Posting Message! Please try again later or contact System Admin']);
        }
    }

    /** 
     * Broadcasted Messages Report
     * */
    public function whatsappBroadcastReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        if (!$validator->passes()) {
            return response()->json([
                'status' => 'Error', 
                'message' => 'Invalid Payload', 
                'error_message' => $validator->errors()->all(),
            ], 500);
        }

        $input = $request->only('start_date', 'end_date');

        try {
            $url = config('services.omniconvo.base_url') . '/api/v1/whatsapp-broadcast-report';
            $token = config('services.omniconvo.token');
            $apikey = $this->apikey;
            $content = [
                'apikey' => $apikey,
                'start_date' => date_for_database($input['start_date']),
                'end_date' => date_for_database($input['end_date']),
            ];

            $client = new \GuzzleHttp\Client();
            $promise = $client->postAsync($url, [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Accept' => "application/json",
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => $content,
            ]);
            
            $successData = [];
            $promise->then(
                function ($response) use(&$successData){
                    $successData = json_decode($response->getBody()->getContents());
                },
                function (\Exception $e) {
                    throw $e;
                }
            );
            $promise->wait();

            return response()->json($successData);
        } catch (\Exception $e) {
            $this->logError($e);
            return response()->json(['status' => 'Error', 'message' => 'Error Fetching Whatsapp Broadcast Report'], 500);
        }
    }
}
