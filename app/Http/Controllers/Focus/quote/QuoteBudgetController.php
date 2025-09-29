<?php

namespace App\Http\Controllers\Focus\quote;

use App\Http\Controllers\Controller;
use App\Models\Access\User\User;
use App\Models\branch\Branch;
use App\Models\Company\Company;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\lead\Lead;
use App\Models\project\Budget;
use App\Models\quote\Quote;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\quote\SendBudgetLink;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use Illuminate\Http\Request;
use Response;
use Yajra\DataTables\Facades\DataTables;
use function Matrix\cofactors;

class QuoteBudgetController extends Controller
{
    protected $headers = [
        "Content-type" => "application/pdf",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0"
    ];

    public function index(Request $request){


        if (!access()->allow('manage-approved-budgets')) return redirect('biller.dashboard');

        $customers = Customer::orderBy('company')
            ->whereHas('quotes', function ($quote) {
                $quote->where('status', 'approved');
            })
            ->get();
        $users = Hrm::all();

        if ($request->ajax()) {

            $quoteBudget = Budget::whereHas('quote', function ($quote) {

                    $quote->whereHas('project', function ($project) {
                            $project->where('status', '!=', 1)
                                ->where('status', '!=', 18);
                        })
                        ->where('status', 'approved')
                        ->when(request('customerFilter'), function ($q) {
                            $q->where('customer_id', request('customerFilter'));
                        });
                })
                ->get()
                ->map(function ($budget) {

                    $prefixes = prefixesArray(['quote', 'proforma_invoice', 'lead', 'invoice'], auth()->user()->ins);

                    $quote = $budget->quote;

                    $link = route('biller.quotes.show', [$quote->id]);
                    if ($quote->bank_id) $link = route('biller.quotes.show', [$quote->id, 'page=pi']);
                    $quoteTid = '<a class="font-weight-bold" href="' . $link . '">' . gen4tid($quote->bank_id ? "{$prefixes[1]}-" : "{$prefixes[0]}-", $quote->tid) . '</a>';

                    $customer = Customer::withoutGlobalScopes()->find($quote->customer_id);
                    $lead = Lead::withoutGlobalScopes()->find($quote->lead_id);
                    $branch = Branch::withoutGlobalScopes()->find($quote->branch_id);

                    $valid_token = token_validator('', 'q' . $quote->id . $quote->tid, true);

                    $technician_url = route('biller.print_technicians_list', [$quote->id, 4, $valid_token, 1]);
                    $stores_url = route('biller.print_stores_list', [$quote->id, 4, $valid_token, 1]);

                    $techniciansTag = '<a href="' . $technician_url . '" class="btn btn-twitter" target="_blank">
                                    <i class="fa fa-print"></i> Technicians
                                </a>';

                    $storesTag = '<a href="' . $stores_url . '" class="btn btn-secondary" target="_blank">
                                    <i class="fa fa-print"></i> Stores
                                </a>';
                    $send_link = '<a class="font-weight-bold click btn btn-primary" data-toggle="modal" quote_id="'.$quote->id.'" 
                     href="#" data-target="#sendlinkModal"><i class="fa fa-mail"></i>Send Link</a>';

                    $creator = User::withoutGlobalScopes()->find($budget->user_id);

                    return (object)[
                        'quote' => $quoteTid,
                        'title' => $quote->notes,
                        'project' => optional($quote->project)->name,
                        'customer' => empty($customer) ? optional($lead)->client_name : optional($customer)->company,
                        'branch' => empty($customer) ? 'N/A' : optional($branch)->name,
                        'created_by' => optional($creator)->fullname ?? 'N/A',
                        'technicians' => $techniciansTag,
                        'stores' => $storesTag,
                        'send_link' => $send_link,
                    ];

                });


            return Datatables::of($quoteBudget)
                ->addColumn('action', function ($model) {

                    if (access()->allow('print-stores-approved-budgets') && !access()->allow('print-technicians-approved-budgets')) return $model->stores;
                    else if (access()->allow('print-technicians-approved-budgets') && !access()->allow('print-stores-approved-budgets')) return $model->technicians;
                    else if (access()->allow('print-stores-approved-budgets') && access()->allow('print-technicians-approved-budgets')) return $model->stores . $model->technicians .$model->send_link;
                })
                ->rawColumns(['quote', 'title', 'action'])
                ->make(true);

        }

        return view('focus.quoteBudgets.index', compact('customers','users'));
    }

    public function send_link_budget(Request $request)
    {
        // dd($request->all());
        try {
            $data = $request->only(['store_users','technicians','note','quote_id','send_email_sms']);
            $data['store_users'] = implode(',',$request->input('store_users',[]));
            $data['technicians'] = implode(',',$request->input('technicians',[]));
            $result = SendBudgetLink::create($data);
            $this->send_email_and_sms($result);
            // dd($data);

        } catch (\Throwable $th) {dd($th);
            //throw $th;
            return errorHandler('Error Sending budget Link', $th);
        }
        return back()->with('flash_success', 'Successfully sent budget link');
    }

    public function send_email_and_sms($data){
        //send sms and emails to Stores and Technicians
        $store_users = explode(',', $data['store_users']);
        $store_phone_numbers = [];
        $store_user_ids = [];
        $store_emails = [];
        $pattern = '/^(07\d{8}|2547\d{8})$/';
        foreach($store_users as $recipient_id){
            $user = Hrm::find($recipient_id);
            if($user->meta){
                $cleanedNumber = preg_replace('/\D/', '', $user->meta->primary_contact);
                if (preg_match($pattern, $cleanedNumber)) {
                    $store_phone_numbers[] = $cleanedNumber;
                    $store_user_ids[] = $user->id;
                    $store_emails[] = $user->email;
                }
            }
        }
        $store_contacts = implode(',', $store_phone_numbers);
        $store_user = implode(',', $store_user_ids);
        //Technicians
        $technicians = explode(',', $data['technicians']);
        $technician_phone_numbers = [];
        $technician_user_ids = [];
        $technician_emails = [];
        $quote = Quote::find($data['quote_id']);
        $prefix = $quote->bank_id ? "PI" : "QT";
        $tid = gen4tid("{$prefix}-", $quote->tid);
        $pattern = '/^(07\d{8}|2547\d{8})$/';
        foreach($technicians as $tech){
            $technician = Hrm::find($tech);
            if($technician->meta){
                $cleanedNumber = preg_replace('/\D/', '', $technician->meta->primary_contact);
                if (preg_match($pattern, $cleanedNumber)) {
                    $technician_phone_numbers[] = $cleanedNumber;
                    $technician_user_ids[] = $technician->id;
                    $technician_emails[] = $technician->email;
                }
            }
        }
        $technician_contacts = implode(',', $technician_phone_numbers);
        $technician_user = implode(',', $technician_user_ids);

        $secureToken = hash('sha256', $data->quote_id . env('APP_KEY'));
        $store_link = route('project_budget.store_list', [
            'quote_id' => $data->quote_id,
            'token' => $secureToken
        ]);
        $token = hash('sha256', $data->quote_id . env('APP_KEY'));
        $technician_link = route('project_budget.technician_list', [
            'quote_id' => $data->quote_id,
            'token' => $token
        ]);
        $company = Company::find(auth()->user()->ins);

        //send sms and emails to stores
        $subject = "From {$company->sms_email_name}: Please find the store list for {$tid}. Given link is: {$store_link} \n\n";
        $subject .= "Additional information: \n\n {$data['note']}";
        $cost_per_160 = 0.6;
        $totalCharacters = strlen($subject);
        $charCount = ceil($totalCharacters/160);
        $count_users = count($store_user_ids);
        $data_mail = [
            'subject' => $subject,
            'user_type' =>'employee',
            'delivery_type' =>'now',
            'message_type' => 'bulk',
            'phone_numbers' => $store_contacts,
            'sent_to_ids' => $store_user,
            'characters' => $charCount,
            'cost' => $cost_per_160,
            'user_count' => $count_users,
            'total_cost' => $cost_per_160*$charCount*$count_users,
            'user_id' => auth()->user()->id,
            'ins' => auth()->user()->ins,

        ];
        if(count($store_users) > 0){
            if($data['send_email_sms'] == 'both' || $data['send_email_sms'] == 'sms'){
                $send_sms = new SendSms();
                $send_sms->fill($data_mail);
                $send_sms->user_id = auth()->user()->id;  // Manually assign user_id
                $send_sms->ins = auth()->user()->ins;
                $send_sms->save();
                (new RosesmsRepository(auth()->user()->ins))->bulk_sms($data_mail['phone_numbers'], $data_mail['subject'], $send_sms);
            }

            if($data['send_email_sms'] == 'both' || $data['send_email_sms'] == 'email'){
                $mail_to = array_shift($store_emails);
                $others = $store_emails;
                //Send EMAILs
                $email_input = [
                    'text' => $subject,
                    'subject' => 'Installation/Store List',
                    'email' => $others,
                    'mail_to' => $mail_to
                ];
                $email = (new RosemailerRepository(auth()->user()->ins))->send_group($email_input['text'], $email_input);
                $email_output = json_decode($email);
                if ($email_output->status === "Success"){

                    $email_data = [
                        'text_email' => $email_input['text'],
                        'subject' => $email_input['subject'],
                        'user_emails' => $email_input['mail_to'],
                        'user_ids' => $store_user,
                        'ins' => auth()->user()->ins,
                        'user_id' => auth()->user()->id,
                        'status' => 'sent'
                    ];
                    SendEmail::create($email_data);
                }
            }
            
            
        }
        $this->technician_send($data,$company, $technician_emails, $technician_contacts, $technician_user_ids, $technician_user, $technician_link, $tid);
        return true;
        
    }
    public function technician_send($input,$company, $technician_emails, $technician_contacts, $technician_user_ids, $technician_user, $technician_link, $tid)
    {
        $subject = "From {$company->sms_email_name}: Please find the store list for {$tid}. Given link is: {$technician_link}  \n\n";
        $subject .= "Additional information: \n\n {$input['note']}";
        $cost_per_160 = 0.6;
        $totalCharacters = strlen($subject);
        $charCount = ceil($totalCharacters/160);
        $count_users = count($technician_user_ids);
        $data = [
            'subject' => $subject,
            'user_type' =>'employee',
            'delivery_type' =>'now',
            'message_type' => 'bulk',
            'phone_numbers' => $technician_contacts,
            'sent_to_ids' => $technician_user,
            'characters' => $charCount,
            'cost' => $cost_per_160,
            'user_count' => $count_users,
            'total_cost' => $cost_per_160*$charCount*$count_users,
            'user_id' => auth()->user()->id,
            'ins' => auth()->user()->ins,

        ];
        if(count($technician_user_ids) > 0){
            if($input['send_email_sms'] == 'both' || $input['send_email_sms'] == 'sms'){
                $send_sms = new SendSms();
                $send_sms->fill($data);
                $send_sms->user_id = auth()->user()->id;  // Manually assign user_id
                $send_sms->ins = auth()->user()->ins;
                $send_sms->save();
                (new RosesmsRepository(auth()->user()->ins))->bulk_sms($data['phone_numbers'], $data['subject'], $send_sms);
            }
            if($input['send_email_sms'] == 'both' || $input['send_email_sms'] == 'email'){
                $mail_to = array_shift($technician_emails);
                $others = $technician_emails;
                //Send EMAILs
                $email_input = [
                    'text' => $subject,
                    'subject' => 'Installation/Picking List',
                    'email' => $others,
                    'mail_to' => $mail_to
                ];
                $email = (new RosemailerRepository(auth()->user()->ins))->send_group($email_input['text'], $email_input);
                $email_output = json_decode($email);
                if ($email_output->status === "Success"){

                    $email_data = [
                        'text_email' => $email_input['text'],
                        'subject' => $email_input['subject'],
                        'user_emails' => $email_input['mail_to'],
                        'user_ids' => $technician_user,
                        'ins' => auth()->user()->ins,
                        'user_id' => auth()->user()->id,
                        'status' => 'sent'
                    ];
                    SendEmail::create($email_data);
                }
            }
            
        }
        return true;
    }

    public function store_list($quote_id, $token)
    {
        $quote = Quote::withoutGlobalScopes()->where('id',$quote_id)->first();
        $company = Company::find($quote->ins);
        $expected_token = hash('sha256', $quote->id . env('APP_KEY'));
        if ($token !== $expected_token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access.'
            ], 403);
        }
        $data = [
            'resource' => $quote,
            'company' => $company
        ];
        $html = view('focus.bill.print_budget_quote_stores', $data)->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);

        $tid = $data['resource']['tid'];
        $name = 'QT-' . sprintf('%04d', $tid) . '_project_budget' . '.pdf';
        if ($data['resource']['bank_id']) {
            $name = 'PI-' . sprintf('%04d', $tid) . '_project_budget' . '.pdf';
        }

        return Response::stream($pdf->Output($name, 'I'), 200, $this->headers);
    }
    public function technician_list($quote_id, $token)
    {
        $quote = Quote::withoutGlobalScopes()->where('id',$quote_id)->first();
        $company = Company::find($quote->ins);
        $expected_token = hash('sha256', $quote->id . env('APP_KEY'));
        if ($token !== $expected_token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access.'
            ], 403);
        }
        $data = [
            'resource' => $quote,
            'company' => $company
        ];
        $html = view('focus.bill.print_budget_quote_technicians', $data)->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);

        $tid = $data['resource']['tid'];
        $name = 'QT-' . sprintf('%04d', $tid) . '_project_budget' . '.pdf';
        if ($data['resource']['bank_id']) {
            $name = 'PI-' . sprintf('%04d', $tid) . '_project_budget' . '.pdf';
        }

        return Response::stream($pdf->Output($name, 'I'), 200, $this->headers);
    }

}
