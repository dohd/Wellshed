<?php

namespace App\Http\Controllers\Focus\recentCustomer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\promotions\PromoCodeReservationController;
use App\Http\Responses\RedirectResponse;
use App\Models\Access\User\User;
use App\Models\customer\Customer;
use App\Models\recentCustomer\RecentCustomerEmail;
use App\Models\recentCustomer\RecentCustomerSms;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Repositories\Focus\general\RosemailerRepository;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RecentCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \DateInvalidOperationException
     */
    public function index(Request $request)
    {

        if (request('lowerDateFilter')) $lowerDateLimit = (new DateTime(request('lowerDateFilter')))->format('Y-m-d');
        else $lowerDateLimit = (new DateTime())->sub(new DateInterval('P30D'))->format('Y-m-d');

        if (request('upperDateFilter')) $upperDateLimit = (new DateTime(request('upperDateFilter')))->format('Y-m-d');
        else $upperDateLimit = (new DateTime())->format('Y-m-d');


        if ($request->ajax()) {

            $recentCustomers = Customer::whereHas('invoices', function ($q) use ($lowerDateLimit) {
                    $q->whereDate('invoicedate', '>=', $lowerDateLimit);
                })
                ->with(['invoices' => function ($q) use ($lowerDateLimit, $upperDateLimit) {
                    $q->whereDate('invoicedate', '>=', $lowerDateLimit)
                        ->whereDate('invoicedate', '<=', $upperDateLimit)
                        ->orderBy('invoicedate', 'desc');
                }])
                ->get()
                ->map(function ($customer) {
                    $lastInvoice = $customer['invoices']->first();

                    return (object) [
                        'id' => $customer->id,
                        'name' => $customer->company,
                        'phone' => $customer->phone,
                        'email' => $customer->email,
                        'address' => $customer->address,
                        'last_invoice' => $lastInvoice
                            ? '<a class="font-weight-bold" target="_blank" href="' . route('biller.invoices.show', $lastInvoice->id) . '">' . gen4tid('INV-', $lastInvoice->tid) . '</a>'
                            : null,
                        'last_invoice_title' => $lastInvoice ? $lastInvoice->notes : null,
                        'last_invoice_date' => $lastInvoice
                            ? (new DateTime($lastInvoice->invoicedate))->format('d/m/Y')
                            : null,
                        'last_invoice_value' => $lastInvoice
                            ? number_format($lastInvoice->total, 2)
                            : null,
                    ];
                })
                ->filter(function ($customer) {
                    return $customer->last_invoice !== null;
                });


            return Datatables::of($recentCustomers)
                ->addColumn('action', function ($model) {

//                return 'TRYZEXXXX';

                    $routeShowCustomer = route('biller.customers.show', $model->id);
                    $routeContactCustomer = route('biller.contact-recent-customer', $model->id);

                    return '<a  target="_blank" href="' . $routeShowCustomer . '" class="btn btn-secondary round mr-1">View</a>' .
                     '<a  target="_blank" href="' . $routeContactCustomer . '" class="btn btn-twitter round mr-1">Reach Out</a>';
                })
                ->rawColumns(['action', 'last_invoice'])
                ->make(true);

        }

        return view('focus.recentCustomers.index', compact('lowerDateLimit', 'upperDateLimit'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function contact($customerId)
    {

        $customer = Customer::where('id' ,$customerId)
            ->get()
            ->map(function ($customer) {

                $lastInvoice = $customer->invoices->first();

                return[
                    'id' => $customer->id,
                    'name' => $customer->company,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'last_invoice' => '<a class="font-weight-bold" target="_blank" href="' . route('biller.invoices.show', $lastInvoice->id) . '">' . gen4tid('INV-', $lastInvoice->tid) . '</a>',
                    'last_invoice_title' => $lastInvoice->notes,
                    'last_invoice_date' => (new DateTime($lastInvoice->invoicedate))->format('d/m/Y'),
                    'last_invoice_value' => number_format($lastInvoice->total, 2),
                ];
            })
            ->first();

        $isProspect = false;

        $customerReservations = (new PromoCodeReservationController())->getCustomerReservations($customerId);

        return view('focus.recentCustomers.contact', compact('customer', 'isProspect', 'customerReservations'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function contactProspect()
    {

        $isProspect = true;

        return view('focus.recentCustomers.contact', compact('isProspect'));
    }


    public function sendEmail(Request $request, $customerId = null){

        $validated = $request->validate([
            'email_address' => ['required', 'email'],
            'subject' => ['required', 'string'],
            'content' => ['required', 'string'],
            'reservations' => ['nullable', 'array'],
            'reservations.*' => ['distinct'],
        ]);


        if(request('prospect_name')) {

            $validated2 = $request->validate([
                'prospect_name' => ['nullable', 'string'],
            ]);

            $validated = array_merge($validated, $validated2);
        }

        try {

            DB::beginTransaction();

            $email_input = [
                'text' => $validated['content'],
                'subject' => $validated['subject'],
                // 'email' => $others,
                'mail_to' => $validated['email_address'],
            ];
            $email = (new RosemailerRepository(Auth::user()->ins))->send($email_input['text'], $email_input);
            $email_output = json_decode($email);
            if ($email_output->status === "Success") {

                $email_data = [
                    'text_email' => $email_input['text'],
                    'subject' => $email_input['subject'],
                    'user_emails' => $email_input['mail_to'],
                    'ins' => Auth::user()->ins,
                    'user_id' => Auth::user()->id,
                    'status' => 'sent'
                ];
                SendEmail::create($email_data);

                if(request('prospect_name')) $newEmail = array_merge($validated, ['prospect_name' => $validated['prospect_name'], 'created_by' => Auth::user()->id]);
                else $newEmail = array_merge($validated, ['customer_id' => $customerId, 'created_by' => Auth::user()->id]);

                $recentCustomerEmail = new RecentCustomerEmail();
                $recentCustomerEmail->fill($newEmail);
                $recentCustomerEmail->save();

                if ($customerId) $recentCustomerEmail->customerPromoCodeReservations()->sync($validated['reservations']);
                else $recentCustomerEmail->thirdPartyPromoCodeReservations()->sync($validated['reservations']);
            }

            DB::commit();
        }
        catch (Exception $ex){

            DB::rollBack();

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return new RedirectResponse(route('biller.recent-customer-messages'), ['flash_success' => "Customer Email Sent Successfully."]);
    }

    public function sendSms(Request $request, $customerId = null){

        $validated = $request->validate([
            'phone_number' => ['required', 'string'],
            'sms_content' => ['required', 'string'],
        ]);


        if(request('prospect_name')) {

            $validated2 = $request->validate([
                'prospect_name' => ['nullable', 'string'],
            ]);

            $validated = array_merge($validated, $validated2);
        }

        try {

            DB::beginTransaction();

            $send_sms = new SendSms();

            $send_sms->subject = $validated['sms_content'];
            $send_sms->phone_numbers = $validated['phone_number'];
            $send_sms->user_id = auth()->user()->id;
            $send_sms->ins = auth()->user()->ins;

            $send_sms->save();


            $recentCustomerSms = new RecentCustomerSms();
            $recentCustomerSms->fill($validated);

            if(request('prospect_name')) $recentCustomerSms->prospect_name = $validated['prospect_name'];
            else $recentCustomerSms->customer_id = $customerId;

            $recentCustomerSms->content = $validated['sms_content'];
            $recentCustomerSms->created_by = Auth::user()->id;
            $recentCustomerSms->save();

//            (new RosesmsRepository(auth()->user()->ins))->bulk_sms($data_mail['phone_numbers'], $data_mail['subject'], $send_sms);

            DB::commit();
        }
        catch (Exception $ex){

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return new RedirectResponse(route('biller.recent-customer-messages'), ['flash_success' => "Customer Sms Sent Successfully."]);
    }



    public function emailTable(){

        $recentEmail = RecentCustomerEmail::
            when(request('emailCustomerFilter'), function ($q) {
                $q->where('customer_id', request('emailCustomerFilter'));
            })
            ->when(request('emailTypeFilter') === 'Customer', function ($q) {
                $q->whereNotNull('customer_id');
            })
            ->when(request('emailTypeFilter') === 'Prospect', function ($q) {
                $q->whereNull('customer_id');
            })
            ->get()
            ->map(function ($email) {

                $customer = $email->customer;
                $sender = User::withoutGlobalScopes()->find($email->created_by);

                return [
                    'id' => $email->id,
                    'customer' => $customer ? optional($customer)->company : $email->prospect_name,
                    'date' => (new DateTime($email->created_at))->format('d/m/Y'),
                    'email' => $email->email_address,
                    'subject' => $email->subject,
                    'content' => $email->content,
                    'sender' => optional($sender)->fullname,
                ];
            });

        return Datatables::of($recentEmail)
            ->addColumn('action', function ($model) {

                $routeShow = route('biller.show-recent-customer-email', $model['id']);

                return '<a  target="_blank" href="' . $routeShow . '" class="btn btn-secondary round mr-1">View</a>';
            })
            ->rawColumns(['action', 'content'])
            ->make(true);
    }


    public function smsTable()
    {

        $recentSms = RecentCustomerSms::
            when(request('smsCustomerFilter'), function ($q) {
                $q->where('customer_id', request('smsCustomerFilter'));
            })
            ->when(request('smsTypeFilter') === 'Customer', function ($q) {
                $q->whereNotNull('customer_id');
            })
            ->when(request('smsTypeFilter') === 'Prospect', function ($q) {
                $q->whereNull('customer_id');
            })
            ->get()
            ->map(function ($sms) {

                $customer = $sms->customer;
                $sender = User::withoutGlobalScopes()->find($sms->created_by);

                return [
                    'id' => $sms->id,
                    'customer' => $customer ? optional($customer)->company : $sms->prospect_name,
                    'date' => (new DateTime($sms->created_at))->format('d/m/Y'),
                    'phone' => $sms->phone_number,
                    'content' => $sms->content,
                    'sender' => optional($sender)->fullname,
                ];
            });


        return Datatables::of($recentSms)
            ->addColumn('action', function ($model) {


                $routeShow = route('biller.show-recent-customer-sms', $model['id']);

                return '<a  target="_blank" href="' . $routeShow . '" class="btn btn-secondary round mr-1">View</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function showRecentMessages()
    {

        $customers = Customer::orderBy('company')
            ->where(function ($query) {
                $query->whereHas('recentCustomerEmail')
                    ->orWhereHas('recentCustomerSms');
            })
            ->get();

        return view('focus.recentCustomers.messages', compact('customers'));
    }


    public function showEmail($messageId)
    {

        $isEmail = true;
        $payload = RecentCustomerEmail::find($messageId);



        return view('focus.recentCustomers.view-message', compact('payload', 'isEmail'));
    }


    public function showSms($messageId)
    {
        $isEmail = false;
        $payload = RecentCustomerSms::find($messageId);

        return view('focus.recentCustomers.view-message', compact('payload', 'isEmail'));
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
