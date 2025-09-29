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
namespace App\Http\Controllers\Focus\send_sms;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\casual\CasualLabourer;
use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use App\Models\Company\SmsSetting;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\hrm\HrmMeta;
use App\Models\project\Project;
use App\Models\prospect\Prospect;
use App\Models\send_sms\SendSms;
use App\Models\supplier\Supplier;
use App\Repositories\Focus\send_sms\SendSmsRepository;
use DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

/**
 * SendSmsController
 */
class SendSmsController extends Controller
{
    /**
     * variable to store the repository object
     * @var SendSmsRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param SendSmsRepository $repository ;
     */
    public function __construct(SendSmsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.send_sms.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \App\Http\Responses\Focus\send_sms\CreateResponse
     */
    public function create()
    {
        $employees = Hrm::all();
        $suppliers = Supplier::all();
        $customers = Customer::all();
        $labourers = CasualLabourer::all();
        $company = Company::find(auth()->user()->ins);
        $company_name = "From ".$company->sms_email_name. ': ';
        $prospect_industries = Prospect::get()->pluck('industry')->unique();
        $prospects = Prospect::all();
        $projects = Project::get()
                    ->map(fn($v) => [
                        'id' => $v->id,
                        'name' => gen4tid('Prj-', $v->tid) . ' - ' . $v->name,
                    ]);
        return view('focus.send_sms.create', compact('employees', 'suppliers', 'customers','labourers','company_name','prospects','prospect_industries','projects'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        
        try {
            //Create the model using repository create method
            $this->repository->create($input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Create send message '.$th->getMessage(), $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.send_sms.index'), ['flash_success' => 'Sms sent successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \App\Http\Responses\Focus\send_sms\EditResponse
     */
    public function edit($send_sms)
    {
        // dd($send_sms);
        $send_sms = SendSms::find($send_sms);
        $employees = Hrm::all();
        $suppliers = Supplier::all();
        $customers = Customer::all();
        $labourers = CasualLabourer::all();
        $company = Company::find(auth()->user()->ins);
        $company_name = "From ".$company->sms_email_name. ': ';
        $prospects = Prospect::all();
        $prospect_industries = Prospect::get()->pluck('industry')->unique();
        $projects = Project::get()
                    ->map(fn($v) => [
                        'id' => $v->id,
                        'name' => gen4tid('Prj-', $v->tid) . ' - ' . $v->name,
                    ]);
        return view('focus.send_sms.edit', compact('send_sms', 'employees', 'labourers','customers','suppliers', 'company_name','prospects','prospect_industries','projects'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, $send_sms)
    {
        // dd($send_sms);
        $send_sms = SendSms::find($send_sms);
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        try {
            //Update the model using repository update method
            $this->repository->update($send_sms, $input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating send message '.$th->getMessage(), $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.send_sms.index'), ['flash_success' => 'Sms Message updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(SendSms $send_sms)
    {
        try {
            //Calling the delete method on repository
            $this->repository->delete($send_sms);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error deleting send message '.$th->getMessage(), $th);
        }
        //returning with successfull message
        return new RedirectResponse(route('biller.send_sms.index'), ['flash_success' => 'Sms Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     * @return \App\Http\Responses\RedirectResponse
     */
    public function maskPasswordInMessage($message)
    {
        // Use regex to find the password in the message
        $pattern = '/Password:\s([\w@#$%^&*]+)/';
        return preg_replace_callback($pattern, function ($matches) {
            $password = $matches[1];
            $maskedPassword = str_repeat('*', strlen($password) - 2) . substr($password, -2);
            return "Password: $maskedPassword";
        }, $message);
    }
    public function show($send_sms)
    {
        $send_sms = SendSms::find($send_sms);
        $send_sms->subject = $this->maskPasswordInMessage($send_sms->subject);
        $user_ids = explode(',', $send_sms->sent_to_ids);
        $phone_numbers = explode(',', $send_sms->phone_numbers);
        $users = [];
        foreach ($user_ids as $user_id){
            if($send_sms->user_type == 'employee'){
                $hrm = Hrm::find($user_id);
                if($hrm){

                    $users[] = $hrm->fullname;
                }
            }else if($send_sms->user_type == 'customer'){
                $customer = Customer::find($user_id);
                if($customer){
                    $users[] = $customer->company ?: $customer->name;
                }
            }
            else if($send_sms->user_type == 'supplier'){
                $supplier = Supplier::find($user_id);
                if($supplier){
                    $users[] = $supplier->company ?: $supplier->name;
                }
            }
            else if($send_sms->user_type == 'labourer'){
                $labourer = CasualLabourer::find($user_id);
                if($labourer){
                    $users[] = $labourer->name;
                }
            }
        }
        $users_sent = [];
        if($send_sms->sms_response){
            foreach ($send_sms->sms_response->sms_callbacks as $callback) {
                $users_sent[] = $callback->msisdn;
            }
        }
        $participants = [];
        if(count($user_ids) == count($phone_numbers)){
            $combined_array = [];
            for ($i = 0; $i < count($user_ids); $i++) {
                $combined_array[] = [
                    'user_id' => $user_ids[$i],
                    'phone' => $phone_numbers[$i]
                ];
            }
            // dd($users_sent);
            foreach ($combined_array as $combined)
            {
                if($send_sms->user_type == 'employee'){
                    // dd($combined['phone']);
                    if (substr($combined['phone'], 0, 2) == '07') {
                        // Replace '07' with '2547'
                        $phone =  '254' . substr($combined['phone'], 1);
                        if (in_array($phone, $users_sent)) {
                            $hrm = Hrm::find($combined['user_id']);
                            $participants[] = $hrm->fullname ?? '';
                        }
                    }else{
                        if (in_array($combined['phone'], $users_sent)) {
                            $hrm = Hrm::find($combined['user_id']);
                            $participants[] = $hrm->fullname ?? '';
                        }
                    }
                    
                }else if($send_sms->user_type == 'customer'){
                    if (substr($combined['phone'], 0, 2) == '07') {
                        $phone =  '254' . substr($combined['phone'], 1);
                        if (in_array($phone, $users_sent)) {
                            $customer = Customer::find($combined['user_id']);
                            $participants[] = $customer->company ?: $customer->name;
                        }
                    }else{
                        if (in_array($combined['phone'], $users_sent)) {
                            $customer = Customer::find($combined['user_id']);
                            $participants[] = $customer->company ?: $customer->name;
                        }
                    }
                }
                else if($send_sms->user_type == 'supplier'){
                    if (substr($combined['phone'], 0, 2) == '07') {
                        $phone =  '254' . substr($combined['phone'], 1);
                        if (in_array($phone, $users_sent)) {
                            $supplier = Supplier::find($combined['user_id']);
                            $participants[] = $supplier->company ?: $supplier->name;
                        }
                    }else{
                        if (in_array($combined['phone'], $users_sent)) {
                            $supplier = Supplier::find($combined['user_id']);
                            $participants[] = $supplier->company ?: $supplier->name;
                        }
                    }
                }
                else if($send_sms->user_type == 'labourer'){
                    if (substr($combined['phone'], 0, 2) == '07') {
                        $phone =  '254' . substr($combined['phone'], 1);
                        if (in_array($phone, $users_sent)) {
                            $labourer = CasualLabourer::find($combined['user_id']);
                            $participants[] = $labourer->name;
                        }
                    }else{
                        if (in_array($combined['phone'], $users_sent)) {
                            $labourer = CasualLabourer::find($combined['user_id']);
                            $participants[] = $labourer->name;
                        }
                    }
                }
            }
        }
        // dd($participants);
        $participant_count = count($participants);
        $user_names = implode(', ',$users);
        $participants = implode(', ',$participants);
        //returning with successfull message
        return new ViewResponse('focus.send_sms.view', compact('send_sms','user_names','participants','participant_count'));
    }


    public function index_sms_settings()
    {
        if(auth()->user()->ins != 2) return back();
        return view('focus.send_sms.index_sms_settings');
    }

    public function get_sms_settings()
    {
        $sms_settings = SmsSetting::withoutGlobalScopes()->where('driver_id',2)->get();
        // dd($sms_settings);
        return DataTables::of($sms_settings)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('driver', function ($send_sms) {
                 return $send_sms->driver;
            })
            ->addColumn('sender', function ($send_sms) {
                 return $send_sms->sender;
            })
            ->addColumn('status', function ($send_sms) {
                $status = '';
                if ($send_sms->active == 1) {
                    $status = '<span style="color: green;"><b>Active</b></span>';
                } else {
                    $status = '<span style="color: red;"><b>Inactive</b></span>';
                }
                return $status;
            })
            ->addColumn('tenant', function ($send_sms) {
                return @$send_sms->company->cname;
           })
            ->addColumn('actions', function ($send_sms) {
                $btn = '<a href="#" title="View" class="view_task success" data-toggle="modal" data-target="#smsSettingsModal" data-id="'. $send_sms->id .'">
                    <i class="ft-eye" style="font-size:1.5em;"></i></a> ';
                return $btn;
            })
            ->make(true);
    }

    public function activate_deactivate_sms(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->only(['id','active']);
            $sms_setting = SmsSetting::withoutGlobalScopes()->find($request['id']);
            if($request['active'] == 1){

                $sms_setting->active = true;
            }else if($request['active'] == 0){
                $sms_setting->active = false;
            }
            $sms_setting->update();
            if($sms_setting){
                DB::commit();
                // return true;
            }
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error updating sms setting '.$th->getMessage(), $th);
        }
        return redirect()->back()->with('flash_success','SmsSetting updated successfully');
    }

    public function index_send_sms(){
        if(auth()->user()->ins != 2) return back();
        $companies = Company::get();
        return view('focus.send_sms.index_send_sms',compact('companies'));
    }

    public function get_all_sms(Request $request)
    {
        $smes = SendSms::withoutGlobalScopes()->whereHas('sms_response')->take(100);
        $smes->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('created_at', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
        });

        $smes->when(request('status'), function ($q){
            $q->whereHas('sms_response',function ($q){
                if (request('status') === 'sent') {
                    $q->whereHas('sms_callbacks');// Ensure there are callbacks (sent)
                } elseif (request('status') === 'not_sent') {
                    $q->whereDoesntHave('sms_callbacks'); // Ensure no callbacks exist (not sent)
                }
            });
        });

        $smes->when(request('customer_id'), function($q){
            $q->where('ins', request('customer_id'));
        });
        

        $good_worth = 0;
        foreach ($smes->get() as $send_sms) {
            $cost_per_160 = 0.6;
            $charCount = strlen($send_sms->subject); // Get the total character count
            $total = $send_sms->total_cost;
            $users = explode(',', $send_sms->phone_numbers); // Split phone numbers into an array
            
            // If the number of characters is not set to 0
            if(numberFormat($send_sms->characters) == 0){
                // Calculate the number of 160-character blocks, rounding up to cover any remaining characters
                $blocks = ceil($charCount / 160);
                // Calculate the total cost by multiplying the cost per block, the number of blocks, and the number of users
                $total = $cost_per_160 * $blocks * count($users);
            }
            $good_worth += $total;
                
        }
        $aggregate = ['good_worth' => numberFormat($good_worth)];
        $smes->get();
        
        // dd($smes);
        return DataTables::of($smes)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('subject', function ($send_sms) {
                 return $this->maskPasswordInMessage($send_sms->subject);
            })
            ->addColumn('message_type', function ($send_sms) {
                return $send_sms->message_type;
            })
            ->addColumn('delivery_type', function ($send_sms) {
                return $send_sms->delivery_type;
            })
            ->addColumn('status', function ($send_sms) {
                $status = '';
                if($send_sms->sms_response){
                    if (count($send_sms->sms_response->sms_callbacks) > 0) {
                        $status = '<span style="color: green;"><b>Sent</b></span>';
                    } 
                    else {
                        $status = '<span style="color: red;"><b>Not Sent</b></span>';
                    }
                }
                
                
                return $status;
            })
            ->addColumn('sent_at', function ($send_sms) {
                $time = '';
                if($send_sms->delivery_type == 'now'){
                   $time = $send_sms->created_at;
                }else{
                    $time = $send_sms->scheduled_date;
                }
                
                
                return $time;
            })
            ->addColumn('total_cost', function ($send_sms) {
                $cost_per_160 = 0.6;
                $charCount = strlen($send_sms->subject); // Get the total character count
                $total = $send_sms->total_cost;
                $users = explode(',', $send_sms->phone_numbers); // Split phone numbers into an array
                
                // If the number of characters is not set to 0
                if(numberFormat($send_sms->characters) == 0){
                    // Calculate the number of 160-character blocks, rounding up to cover any remaining characters
                    $blocks = ceil($charCount / 160);
                    // Calculate the total cost by multiplying the cost per block, the number of blocks, and the number of users
                    $total = $cost_per_160 * $blocks * count($users);
                }
                
                return numberFormat($total);
           })
            ->addColumn('aggregate', function () use ($aggregate){
                return $aggregate;
            })
            ->addColumn('actions', function ($send_sms) {
                $btn = '<a href="#" title="View" class="view_task success" data-toggle="modal" data-target="#smsSettingsModal" data-id="'. $send_sms->id .'">
                    <i class="ft-eye" style="font-size:1.5em;"></i></a> ';
                return $btn;
            })
            ->make(true);
    }

    //sms receipients settings
    public function notification_email_sms(){
        $recipients = Hrm::all();
        $all_users = Hrm::withoutGlobalScopes(['ins'])->with('business')->get();
        $recipient_settings = RecipientSetting::all();
        return view('focus.general.notification_email_sms', compact('recipients','recipient_settings','all_users'));
    }

    public function store_recipents(Request $request){
        try {
            $data = $request->only(['title','type','uom','target','sms','email']);
            $recipients = implode(',',$request->input('recipients',[]));
  
            $data['recipients'] = $recipients;
            $data['user_id'] = auth()->user()->id;
            $data['ins'] = auth()->user()->ins;
            DB::beginTransaction();
            $setting_type_exists = RecipientSetting::where('type', $data['type'])->exists();
            if($setting_type_exists == true) throw ValidationException::withMessages(["The setting type selected already not exists!!"]);
            if($data['type'] == 'project_percentage' || $data['type'] == 'project_amount'){
                $data['latest_project_id'] = Project::latest()->first()->id;
            }
            $result = RecipientSetting::create($data);
            if ($result){
                DB::commit();
            }
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error creating recipient', $th);
        }
        return back()->with('flash_success', 'Recipient Created Successfully');
    }

    public function get_settings(Request $request)
    {
        $sms_recipients = RecipientSetting::find($request->id);
        return response()->json($sms_recipients);
    }

    public function update_settings(Request $request){
        // dd($request->all());
        try {
            $data = $request->only(['title','type','uom','target','sms','email']);
            $recipients = implode(',',$request->input('recipients',[]));
  
            $data['recipients'] = $recipients;
            $result = RecipientSetting::find($request->id);
            DB::beginTransaction();
            $result->update($data);
            if ($result){
                DB::commit();
            }
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error updating recipient', $th);
        }
        return back()->with('flash_success', 'Recipient Updating Successfully');
    }

    public function delete_settings(Request $request)
    {
        $result = RecipientSetting::find($request->id);
        $result->delete();
        return response()->json(['success'=>'success','status' => 'Success', 'message' => 'Recipient Successfully Deleted']);
    }

    public function get_prospects(Request $request)
    {
        $prospects = Prospect::where('industry', $request->industry)->get();
        return response()->json($prospects);
    }
    public function get_casuals(Request $request)
    {
        $project = Project::with('labour_allocations.casualLabourers')->find($request->project_id);
        // dd($project->labour_allocations);
        $labourers = $project->labour_allocations ? $project->labour_allocations->flatMap->casualLabourers : null;
        return response()->json($labourers);
    }

}
