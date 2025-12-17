<?php

namespace App\Http\Controllers\Focus\whatsapp;

use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Models\Company\Company;
use App\Models\meta_whatsapp\MetaWhatsappThread;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Log;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class WhatsappController extends Controller
{
    public function logError($e)
    {
        return Log::error($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
    }

    /**
     * Setup Page
     */
    public function setup(Request $request)
    {
        $business = auth()->user()->business;
        return new ViewResponse('focus.whatsapp.setup', compact('business'));
    }

    /**
     * Templates Landing Page
     */
    public function templates(Request $request)
    {
        $business = auth()->user()->business;
        return new ViewResponse('focus.whatsapp.templates.index', compact('business'));
    }

    /**
     * Templates Create Page
     */
    public function templates_create(Request $request)
    {
        $business = auth()->user()->business;
        return new ViewResponse('focus.whatsapp.templates.create', compact('business'));
    }

    /**
     * Templates Store
     */
    public function templates_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'category' => 'required',
            'body_content' => 'required',
        ]);
        if (!$validator->passes()) {
            return response()->json([
                'status' => 'Error', 
                'message' => 'Invalid Payload', 
                'error_message' => $validator->errors()->all(),
            ], 500);
        }
        $input = $request->only([
            'name', 'category', 'header_content', 'body_content', 'footer_content',
            'variable_type', 'variable',
        ]);
        $input['name'] = trim($input['name']);
        
        try {
            $business = Company::where('is_main', 1)->first() ?? optional(auth()->user())->business;
            $url = $business->graph_api_url .  "/{$business->whatsapp_business_account_id}";
            $token = $business->whatsapp_access_token;

            $jsonData = [
                'name' => $input['name'],
                'language' => 'en',
                'category' => $input['category'],
                'components' => [],
            ];
            // Body component
            $bodyComponent = ['type' => 'BODY', 'text' => $input['body_content']];
            $bodyVars = array_map(fn($v) => $v['variable'], array_filter(
                    modify_array($request->only('variable_type', 'variable')),
                    fn($v) => $v['variable_type'] === 'body'
                )
            );
            if (!empty($bodyVars)) {
                $bodyComponent['example']['body_text'][] = $bodyVars;
            }
            $jsonData['components'][] = $bodyComponent;

            // dd($jsonData);

            $client = new \GuzzleHttp\Client();
            $promise = $client->postAsync($url . '/message_templates', [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Accept' => "application/json",
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => $jsonData,
            ]);
            
            $successData = [];
            $promise->then(
                function ($response) use(&$successData, $input){
                    $successData = json_decode($response->getBody()->getContents());
                },
                function (\Exception $e) {
                    throw $e;
                }
            );
            $promise->wait();

            return response()->json([
                'status' => 'Success', 
                'message' => 'Message Template created successfully',
                'redirectTo' => route('biller.whatsapp.templates.index'),
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            // Capture Meta Error
            if ($e instanceof RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $errorBody = (string) $response->getBody();
                // Try to decode JSON error message
                $errorData = json_decode($errorBody, true);
                $msg = $errorData['error']['error_user_msg'] ?? $errorData['error'];
                Log::error($msg);
                return response()->json([
                    'status' => 'Error', 
                    'message' => $msg,
                ], 500);
            } 

            return response()->json([
                'status' => 'Error', 
                'message' => 'Error creating message Template!',
            ], 500);
        }
    }

    /**
     * Template Destroy
     * */
    public function template_destroy(Request $request)
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
     * Messages Landing Page
     */
    public function messages(Request $request)
    {
        $business = auth()->user()->business;
        return new ViewResponse('focus.whatsapp.messages', compact('business'));
    }

    public function messagesDataTable()
    {
        $q = MetaWhatsappThread::whereNotNull('status');

        return DataTables::of($q)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('status', function($q) {
                if ($q->status === 'read') {
                    return '<span class="badge bg-primary">'. $q->status .'</span>';
                }
                return '<span class="badge bg-secondary">'. $q->status .'</span>';
            })
            ->editColumn('timestamp', function($q) {
                return Carbon::createFromTimestamp($q->timestamp);
            })
            ->make(true);
    }

    /**
     * Messages Create Page
     */
    public function messages_create(Request $request)
    {
        $business = auth()->user()->business;

        return new ViewResponse('focus.whatsapp.create', compact('business'));
    }

    /**
     * Messages Store
     */
    public function messages_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_name' => 'required',
            // 'user_segment' => 'required',
            'phone_no' => 'nullable|required_if:user_segment,local',
            // 'csv_file' => 'nullable|required_if:user_segment,import|file|mimes:csv,txt',
        ]);
        if (!$validator->passes()) {
            return response()->json([
                'status' => 'Error', 
                'message' => 'Invalid Payload', 
                'error_message' => $validator->errors()->all(),
            ], 500);
        }

        $input = $request->only([
            'template_name', 'template_id', 'user_segment', 'phone_no', 'import_file',
            'variable_type', 'variable', 'has_buttons',
        ]);
        
        try {
            // broadcast 
            if (is_array($input['phone_no']) || @$input['user_segment'] == 'import') {
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
            }

            $business = Company::where('is_main', 1)->first() ?? optional(auth()->user())->business;
            $url = $business->graph_api_url . "/{$business->whatsapp_phone_no_id}";
            $token = $business->whatsapp_access_token;

            $jsonData = [
                "messaging_product" => "whatsapp",
                "to" => $input['phone_no'],
                "type" => "template",
                "template" => [
                    "name" => $input['template_name'],
                    "language" => [
                        "code" => "en"
                    ],
                    "components" => [],
                ],
            ];

            // Body component
            $bodyComponent = ['type' => 'body', "parameters" => []];
            $bodyVars = array_map(fn($v) => $v['variable'], array_filter(
                    modify_array($request->only('variable_type', 'variable')),
                    fn($v) => $v['variable_type'] === 'body'
                )
            );
            if (!empty($bodyVars)) {
                $bodyComponent['parameters'] = array_map(fn($v) => [
                    'type' => 'text',
                    'text' => $v,
                ], $bodyVars);
            }
            $jsonData['template']['components'][] = $bodyComponent;

            // Buttons component
            $buttonTypes = $request->button_type;
            if (!empty($buttonTypes)) {
                foreach ($buttonTypes as $key => $type) {
                    $jsonData['template']['components'][] = [
                        'type' => 'button',
                        'sub_type' => $type,
                        'index' => $key,
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => 'https://',                               
                            ],
                        ],
                    ];
                }
            }

            // dd($jsonData);
        
            $client = new \GuzzleHttp\Client();
            $promise = $client->postAsync($url . '/messages', [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Accept' => "application/json",
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => $jsonData,
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

            return response()->json([
                'status' => 'Success', 
                'message' => 'Message Posted Successfully',
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            // Capture Meta Error
            if ($e instanceof RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $errorBody = (string) $response->getBody();
                // Try to decode JSON error message
                $errorData = json_decode($errorBody, true);
                $msg = $errorData['error']['error_data']['details'] ?? $errorData['error']['message'];
                Log::error($errorData['error']);
                return response()->json([
                    'status' => 'Error', 
                    'message' => $msg,
                ], 500);
            } 

            return response()->json([
                'status' => 'Error', 
                'message' => 'Error posting message! Please try again later or contact system admin',
            ], 500);
        }
    }
}
