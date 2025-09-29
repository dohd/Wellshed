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

use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Jobs\UpdateChatByBotUsers;
use App\Models\Company\Company;
use App\Models\lead\AgentLead;
use App\Models\lead\Lead;
use App\Models\lead\OmniChat;
use App\Models\lead\OmniMessage;
use App\Models\sms_response\SmsCallback;
use Illuminate\Support\Facades\Cache;
use Log;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class AgentLeadsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        $agent_lead = AgentLead::first();
        $locations = AgentLead::distinct('location')->pluck('location');
        $productBrands = AgentLead::distinct('product_brand')->pluck('product_brand');
        
        return new ViewResponse('focus.agent_leads.index', compact('agent_lead', 'locations', 'productBrands'));
    }

    // Leads Datatable
    public function datatable()
    {
        $q = AgentLead::query();
        $q->when(request('location'), fn($q) => $q->where('location', request('location')));
        $q->when(request('product_brand'), fn($q) => $q->where('product_brand', request('product_brand')));
        $q->when(request('user_type'), function($q) {
            $q->whereHas('omniChat', fn($q) => $q->where('user_type', request('user_type')));
        });
        $q->when(request('quote_status') == 'quoted', fn($q) => $q->where('quote_status', request('quote_status')));
        $q->when(request('quote_status') == 'none', fn($q) => $q->whereNull('quote_status'));

        $q->when(request('start_date') && request('end_date'), function ($q) {
            $dateRange = array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]);
            $q->whereDate('created_at', '>=', $dateRange[0])->whereDate('created_at', '<=', $dateRange[1]);
        });

        $q->latest();
        return DataTables::of($q)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('row_check', function ($q) {
                if ($q->lead) return '<input checked disabled type="checkbox" class="row-check" data-id="'. $q->id .'">';
                return '<input type="checkbox" class="row-check" data-id="'. $q->id .'">';
            })
            ->editColumn('client_name', function ($q) {
                return '<b>'. $q->client_name .'</b>';
            })
            ->editColumn('email', function ($q) {
                return '<b>'. $q->email .'</b>';
            })
            ->addColumn('created_at', function ($q) {
                return '<span class="d-none">'. strtotime(dateFormat($q->created_at)) .'</span> ' . dateFormat($q->created_at);
            })
            ->make(true);
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
            'client_name' => 'required',
            'phone_no' => 'required',
            'product_brand' => 'required',
            'product_spec' => 'required',
        ]);
        if (!$validator->passes()) {
            return response()->json(['status' => 'Error', 'message' => 'Invalid Payload', 'error_message' => $validator->errors()->all()], 500);
        }
        
        $input = $request->only(['client_name', 'phone_no', 'email', 'project', 'location', 'product_brand', 'product_spec']);

        try {
            $unauthorized = true;
            $auth_header = $request->header('authorization');
            if ($auth_header) {
                $credentials = str_replace('Basic ', '', $auth_header);
                $credentials = base64_decode($credentials);
                $credentials = explode(':', $credentials);
                if (count($credentials) == 2) {
                    $agentKey = $credentials[0]; // uuid
                    $email = $credentials[1]; // email
                    $company = Company::where('agent_key', $agentKey)->where('email', $email)->first();
                    if ($company) {
                        $unauthorized = false;
                        $input['ins'] = $company->id;
                        $agent_lead = AgentLead::create($input);
                        // $agent_lead = null;
                    }
                }
            } 
            if ($unauthorized) 
                return response()->json(['status' => 'Error', 'message' => 'Unauthorized Access'], 401);
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::error($th->getMessage() . ' at ' . $th->getFile() . ':' . $th->getLine());
            return response()->json(['status' => 'Error', 'message' => 'Error Saving Lead'], 500);
        }
            
        return response()->json(['status' => 'Success', 'message' => 'Lead Saved Successfully', 'payload' => $agent_lead]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\lead\Lead $lead
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Request $request)
    { 
        if ($request->checked_ids) {
            $delete_ids = explode(',', $request->checked_ids);
            AgentLead::whereIn('id', $delete_ids)->delete();
        } else {
            return new RedirectResponse(route('biller.agent_leads.index'), ['flash_error' => 'Error Deleting Leads']);
        }

        return new RedirectResponse(route('biller.agent_leads.index'), ['flash_success' => 'Lead Successfully Deleted']);
    }

    /**
     * Show the view for the specific resource
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param \App\Models\lead\Lead $lead
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Lead $lead, Request $request)
    {
        return new ViewResponse('focus.agent_leads.view', compact('lead'));
    }

    /**
     * @throws \Exception
     */
    public function newAgentLeadsMetrics(){

        $newLeads = array_fill(0, 6, 0);
        $months = array_fill(0, 6, 'N/A');


        for ($u = 5; $u >= 0; $u--){

            $date = (new DateTime())->sub(new DateInterval('P' . $u . 'M'));

            $customers = count(AgentLead::whereMonth('created_at', $date->format('m'))->get());

            $newLeads[count($months)-1 - $u] += $customers;
            $months[count($months)-1 - $u] = $date->format("M");
        }

        $title = "New AI Agent Leads For The Period " . (new DateTime($months[0]))->format('F') . " to " . (new DateTime($months[count($months)-1]))->format('F') . " " . (new DateTime())->format('Y');


        return compact('title','newLeads', 'months');
    }

    public function sms_callback(Request $request){
        $data = $request->json()->all();

        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'deliveryStatus' => 'required|string',
                'deliveryTime' => 'required|date_format:Y-m-d H:i:s',
                'reference' => 'required|string',
                'msisdn' => 'required|string',  // Adjust as needed (e.g., 'digits:12')
                'cost' => 'required|numeric',   // Ensure cost is numeric
                'sender' => 'required|string',
            ]);
            $result = [
                'delivery_status' => $validatedData['deliveryStatus'],
                'delivery_time' => $validatedData['deliveryTime'],
                'reference' => $validatedData['reference'],
                'msisdn' => $validatedData['msisdn'],
                'cost' => $validatedData['cost'],
                'sender' => $validatedData['sender'],
            ];
        
            // Store the callback data in the database
            SmsCallback::create($result);
        
            // Log the incoming request
            //\Log::info('SMS Callback received', $validatedData);
        } catch (\Throwable $th) {
            //throw $th;
            return $th->getMessage();
        }
    
        // Return a success response
        return response()->json([
            'status' => 'success',
            'message' => 'Callback received and processed',
        ], 200);
    }

    /**
     * Chats Transcripts index page
     */
    public function omniTranscripts()
    {
        // Dispatch Job: Update chats by Bot user
        if (session('omniconvoAccessToken')) {
            $omniUser = @auth()->user()->business->omniUser;
            $credentials = ['apiKey' => @$omniUser->api_key, 'email' => @$omniUser->email];
            Cache::put('omniconvoAccessToken', session('omniconvoAccessToken'), now()->addMinutes(10));
            if (array_filter($credentials)) {
                UpdateChatByBotUsers::dispatch($credentials);
            }
        } else {
            Log::error('Chat Transcripts Index: missing Omniconvo access token');
        }

        $chats = OmniChat::all();

        return view('focus.agent_leads.omni_transcripts', compact('chats'));
    }

    /**
     * Chats Analytics index page
     */
    public function omniAnalyitcs()
    {
        // summaries
        $botUsersCt = OmniChat::count();
        $humanHelpCt = OmniChat::whereHas('messages', fn($q) => $q->whereNotNull('from_user_id'))
            ->count();
        $inMsgCt = OmniMessage::whereHas('chat')->whereNull('bot_id')->count();
        $outMsgCt = OmniMessage::whereHas('chat')->whereNotNull('bot_id')->count();

        // charts
        $usersByDate = OmniChat::selectRaw('DATE(created_at) date, COUNT(*) count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        $usersByType = OmniChat::selectRaw("COALESCE(user_type, 'others') user_type, COUNT(*) count")
            ->groupBy('user_type')
            ->get();
        $usersByDay = OmniChat::selectRaw("DAYNAME(created_at) dayname, COUNT(*) count")
            ->groupBy('dayname')
            ->orderByRaw("FIELD(DAYNAME(created_at),'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
            ->get();
        $topMsgCt = OmniMessage::whereHas('chat')
            ->selectRaw('message, COUNT(*) count')
            ->where('message', '!=', '')
            ->groupBy('message')
            ->orderBy('count', 'DESC')
            ->limit(5)
            ->get();

        return view('focus.agent_leads.omni_analytics', compact(
            'botUsersCt', 'humanHelpCt', 'inMsgCt', 'outMsgCt',
            'usersByDate', 'usersByType', 'usersByDay', 'topMsgCt',
        ));
    }

    /**
     * Bot Contacts index page
     */
    public function omniContacts()
    {
        return view('focus.agent_leads.omni_contacts');
    }

    public function omniContactsDatatable()
    {
        $query = OmniChat::whereNotNull('user_type')
        ->when(request('source'), fn($q) => $q->where('user_type', request('source')))
        ->with(['lastMessage']);

        return Datatables::of($query)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('last_converse', function($chat) {
            $date = @$chat->lastMessage->date;
            if ($date) $date = dateFormat($date, 'd-M-Y H:i');
            return $date;
        })
        ->make(true);
    }
}
