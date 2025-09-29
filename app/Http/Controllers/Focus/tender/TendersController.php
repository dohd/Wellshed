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
namespace App\Http\Controllers\Focus\tender;

use App\Models\tender\Tender;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use App\Models\hrm\Hrm;
use App\Models\lead\Lead;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\tender\TenderFollowup;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use App\Repositories\Focus\tender\TenderRepository;
use Illuminate\Support\Str;

/**
 * tendersController
 */
class TendersController extends Controller
{
    /**
     * variable to store the repository object
     * @var tenderRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param tenderRepository $repository ;
     */
    public function __construct(TenderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\tender\ManagetenderRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.tenders.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatetenderRequestNamespace $request
     * @return \App\Http\Responses\Focus\tender\CreateResponse
     */
    public function create()
    {
        $leads = Lead::where('status', 0)->orderBy('id', 'desc')->get();
        $prefixes = prefixesArray(['quote', 'lead'], auth()->user()->ins);
        $users = Hrm::all();
        return view('focus.tenders.create', compact('leads','prefixes','users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoretenderRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        $team_member_ids = implode(',',$request->input('team_member_ids',[]));
        $input['team_member_ids'] = $team_member_ids;
        try {
            //Create the model using repository create method
            $this->repository->create($input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Creating Tender', $th);
        }
        
        //return with successfull message
        return new RedirectResponse(route('biller.tenders.index'), ['flash_success' => 'Tender Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\tender\tender $tender
     * @param EdittenderRequestNamespace $request
     * @return \App\Http\Responses\Focus\tender\EditResponse
     */
    public function edit(Tender $tender)
    {
        $leads = Lead::where('status', 0)->orderBy('id', 'desc')->get();
        $prefixes = prefixesArray(['quote', 'lead'], auth()->user()->ins);
        $users = Hrm::all();
        return view('focus.tenders.edit', compact('tender', 'leads','prefixes','users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatetenderRequestNamespace $request
     * @param App\Models\tender\tender $tender
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, Tender $tender)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $team_member_ids = implode(',',$request->input('team_member_ids',[]));
        $input['team_member_ids'] = $team_member_ids;
        try {
            //Update the model using repository update method
            $this->repository->update($tender, $input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Tender', $th);
        }
        
        //return with successfull message
        return new RedirectResponse(route('biller.tenders.index'), ['flash_success' => 'Tender Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletetenderRequestNamespace $request
     * @param App\Models\tender\tender $tender
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Tender $tender)
    {
        //Calling the delete method on repository
        $this->repository->delete($tender);
        //returning with successfull message
        return new RedirectResponse(route('biller.tenders.index'), ['flash_success' => 'Tender Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletetenderRequestNamespace $request
     * @param App\Models\tender\tender $tender
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Tender $tender)
    {

        //returning with successfull message
        $users = Hrm::all();
        return new ViewResponse('focus.tenders.view', compact('tender','users'));
    }

    public function change_status(Request $request, $tender_id)
    {
        try {
            $data = $request->only(['tender_stages']);
            $tender = Tender::find($tender_id);
            if($data['tender_stages'] == 'won'){
                $data['message'] = $request->message;
                $data['won_date'] = date_for_database($request->won_date);
                $employee_ids = implode(',',$request->input('employee_ids',[]));
                $data['employee_ids'] = $employee_ids;
            }
            $tender->update($data);
            if($tender->tender_stages == 'won')
            {
                $this->get_users($tender['employee_ids'], $tender);
            }
        } catch (\Throwable $th) {dd($th);
            //throw $th;
            return errorHandler('Error Changing Status', $th);
        }
        return back()->with('flash_success','Tender Stage Changed Successfully!!');
    }

    public function get_users($user_ids, $result)
    {
        $ids = explode(',', $user_ids);
        $users = Hrm::whereIn('id', $ids)->get();
        foreach($users as $user)
        {
            $this->notify_users($user, $result);
        }
    }

    public function notify_users($user, $tender)
    {
        if (!$user) return;

        $phone_number = null;
        $user_id = $user->id;
        $userName = $user->fullname ?? '';
        $userEmail = $user->email ?? '';

        $pattern = '/^(0[17]\d{8}|254[17]\d{8})$/';
        if ($user->meta) {
            $cleanedNumber = preg_replace('/\D/', '', $user->meta->primary_contact);
            if (preg_match($pattern, $cleanedNumber)) {
                $phone_number = preg_match('/^01\d{8}$/', $cleanedNumber)
                    ? '254' . substr($cleanedNumber, 1)
                    : $cleanedNumber;
            }
        }

        $lead = $tender->lead;
        $clientname = $tender->client->company ?? $lead->client_name ?? '';
        $branch = $tender->branch->name ?? $lead->branch->name ?? '';
        $lead_no = $lead ? gen4tid('TKT-', $lead->reference) : '';

        $company = Company::find(auth()->user()->ins);
        $setting = RecipientSetting::where('type', 'tender_notification')
            ->where('ins', $company->id)
            ->first();
        $companyName = 'From ' . Str::title($company->sms_email_name) . ':';

        $smsText = "$companyName Dear $userName, Tender Won! Ticket: {$lead_no}, Customer: {$clientname}, Branch: {$branch}, Date: {$tender->won_date}, Subject: {$tender->title}.";
        $emailText = "Dear Team,\n\nWe are excited to announce that a tender has been successfully won. Please find the details below:\n\n- 🌻 Ticket Number: {$lead_no}\n- 🏢 Customer: {$clientname}\n- 📍 Branch: {$branch}\n- 🗓 Date Won: {$tender->won_date}\n- 📍 Subject: {$tender->title}\n\nKindly proceed with the required actions and next steps. For more details, contact the procurement team or refer to the tender documentation.\n\nBest regards,\n{$company->cname}";

        // Send SMS
        if ($phone_number && $setting->sms === 'yes') {
            $charCount = strlen($smsText);
            $blocks = ceil($charCount / 160);
            $cost_per_160 = 0.6;

            $smsData = [
                'user_type' => 'employee',
                'delivery_type' => 'now',
                'message_type' => 'single',
                'phone_numbers' => $phone_number,
                'sent_to_ids' => $user_id,
                'subject' => $smsText,
                'characters' => $charCount,
                'cost' => $cost_per_160,
                'user_count' => 1,
                'total_cost' => $cost_per_160 * $blocks,
            ];

            $smsResult = SendSms::create($smsData);
            (new RosesmsRepository($company->id))->textlocal($phone_number, $smsText, $smsResult);
        }

        // Send Email
        if ($userEmail && $setting->email === 'yes') {
            $emailInput = [
                'subject' => 'Tender Won Notification',
                'mail_to' => $userEmail,
                'name' => $userName,
                'text' => $emailText
            ];

            $emailOutput = json_decode((new RosemailerRepository($company->id))->send($emailText, $emailInput));

            if ($emailOutput->status === 'Success') {
                SendEmail::create([
                    'text_email' => $emailText,
                    'subject' => $emailInput['subject'],
                    'user_emails' => $userEmail,
                    'user_ids' => $user_id,
                    'ins' => $company->id,
                    'user_id' => auth()->user()->id,
                    'status' => 'sent'
                ]);
            }
        }
    }


    public function store_follow_ups(Request $request, $tender_id)
    {
        try {
            $data = $request->only(['recipient','remark','date','reminder_date']);
            $data['tender_id'] = $tender_id;
            foreach ($data as $key => $val) {
                if (in_array($key, ['date', 'reminder_date']))
                    $data[$key] = date_for_database($val);
            }  
            TenderFollowup::create($data);
            
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Creating Tender follow ups', $th);
        }
        return back()->with('flash_success','Tender Follow up Added Successfully!!');
    }

    public function get_follow_ups(Request $request)
    {
        $follow_up = TenderFollowup::find($request->id);
        return response()->json($follow_up);
    }

    public function update_follow_ups(Request $request, $tender_id)
    {
        try {
            $data = $request->only(['recipient','remark','date','reminder_date','id']);
            $data['tender_id'] = $tender_id;
            foreach ($data as $key => $val) {
                if (in_array($key, ['date', 'reminder_date']))
                    $data[$key] = date_for_database($val);
            }  
            $follow_up = TenderFollowup::find($request->id);
            $follow_up->update($data);
            
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Tender follow ups', $th);
        }
        return back()->with('flash_success','Tender Follow up Updated Successfully!!');
    }
    
    public function delete_follow_ups($follow_up_id)
    {
        try {
            
            $follow_up = TenderFollowup::find($follow_up_id);
            $follow_up->delete();
            
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Deleting Tender follow ups', $th);
        }
        return back()->with('flash_success','Tender Follow up Deleted Successfully!!');
    }
}
