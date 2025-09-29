<?php

namespace App\Http\Controllers\Focus\rfq;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\budget\BudgetsController;
use App\Http\Controllers\Focus\omniconvo\OmniController;
use App\Http\Requests\Focus\rfq\CreateRfQRequest;
use App\Http\Requests\Focus\rfq\DeleteRfQRequest;
use App\Http\Requests\Focus\rfq\EditRfQRequest;
use App\Http\Requests\Focus\rfq\ManageRfQRequest;
use App\Http\Requests\Focus\rfq\StoreRfQRequest;
use App\Http\Requests\Focus\rfq\UpdateRfQRequest;
use App\Models\additional\Additional;
use App\Models\Company\Company;
use App\Models\pricegroup\Pricegroup;
use App\Models\project\Budget;
use App\Models\purchase_request\PurchaseRequest;
use App\Models\rfq\RfQItem;
use App\Models\term\Term;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\rfq\RfQRepository;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use App\Http\Responses\ViewResponse;
use App\Models\supplier\Supplier;
use App\Http\Responses\Focus\rfq\CreateResponse;
use App\Http\Responses\Focus\rfq\EditResponse;
use App\Http\Responses\RedirectResponse;
use App\Models\rfq\RfQ;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use BillDetailsTrait;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class RfQController extends Controller
{
    protected $repository;

    public function __construct(RfQRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ManageRfQRequest $request)
    {
//        return $this->printRfq(2);

        $suppliers = Supplier::whereHas('purchase_orders')->get(['id', 'name']);

        return new ViewResponse('focus.rfq.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        return new CreateResponse('focus.rfq.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRfQRequest $request)
    {

        $request->validated();

        $rfq = $request->only(['tid', 'date', 'due_date', 'subject', 'tax','term_id','credit_terms']);
        $supplier_ids = implode(',',$request->input('supplier_ids',[]));
        $rfq['supplier_ids'] = $supplier_ids;
        $purchase_requisition_ids = implode(',',$request->input('purchase_requisition_ids',[]));
        $rfq['purchase_requisition_ids'] = $purchase_requisition_ids;
        $rfqItems = $request->only([
            'item_id', 'description', 'uom', 'itemproject_id', 'qty', 'type', 'product_code', 'warehouse_id','project_id','project_milestone_id','purchase_requisition_item_id'
        ]);
        // dd($rfq, $rfqItems);

        $rfq['ins'] = auth()->user()->ins;
        $rfq['user_id'] = auth()->user()->id;
        // modify and filter items without item_id
        $rfqItems = modify_array($rfqItems);
        $rfqItems = array_filter($rfqItems, function ($v) {
            return $v['item_id'];
        });

//        return compact('rfq', 'rfqItems');

        try{
            DB::beginTransaction();

            $newRfq = new RfQ();
            $newRfq->fill($rfq);
            $newRfq->date = (new DateTime($rfq['date']))->format('Y-m-d');
            $newRfq->due_date = (new DateTime($rfq['due_date']))->format('Y-m-d');

            $newRfq->save();

            foreach ($rfqItems as $item){

                $newRfqItem = new RfQItem();

                $newRfqItem->fill($item);

                $newRfqItem->rfq_id = $newRfq->id;
                $newRfqItem->type = strtoupper($item['type']);

                if ($item['type'] === 'Stock') {

                    $newRfqItem->product_id = $item['item_id'];
                    // $newRfqItem->project_id = $newRfq->project_id;
                }
                else if ($item['type'] === 'Expense') {

                    $newRfqItem->expense_account_id = $item['item_id'];
                    $newRfqItem->project_id = $newRfq->project_id;;
                }

                $newRfqItem->quantity = $item['qty'];

                $newRfqItem->save();
            }

            DB::commit();
        }
        catch (Exception $ex) {

            DB::rollBack();
            return errorHandler('Error Updating Direct Purchase', $ex);
        }



        return new RedirectResponse(route('biller.rfq.index'), ['flash_success' => 'RFQ created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ManageRfQRequest $request, $id)
    {
        $rfq = RfQ::where('id', $id)->with(['items.product', 'items.account'])->first();
        $supplier_ids = explode(',',$rfq->supplier_ids);
        $suppliers = Supplier::whereIn('id',$supplier_ids)->get();
        return new ViewResponse('focus.rfq.view', compact('rfq','suppliers'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(EditRfQRequest $request, $id)
    {
        $rfq = RfQ::find($id);
        return new EditResponse($rfq);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRfQRequest $request, $id)
    {

        $request->validated();

        $rfq = $request->only(['tid', 'date', 'due_date', 'subject', 'tax','term_id','credit_terms']);
        $supplier_ids = implode(',',$request->input('supplier_ids',[]));
        $rfq['supplier_ids'] = $supplier_ids;
        $rfqItems = $request->only([
            'id', 'item_id', 'description', 'uom', 'itemproject_id', 'qty', 'type', 'product_code', 'warehouse_id','project_id','project_milestone_id','purchase_requisition_item_id'
        ]);

        // modify and filter items without item_id
        $rfqItems = modify_array($rfqItems);
        $rfqItems = array_filter($rfqItems, function ($v) {
            return $v['item_id'];
        });

//        return compact('rfq', 'rfqItems');

        try{
            DB::beginTransaction();

            $editedRfq = RfQ::find($id);
            $editedRfq->fill($rfq);
            $editedRfq->date = (new DateTime($editedRfq['date']))->format('Y-m-d');
            $editedRfq->due_date = (new DateTime($editedRfq['due_date']))->format('Y-m-d');
            $editedRfq->save();

            foreach ($rfqItems as $item){

                if(empty($item['id'])) $editedRfqItem = new RfQItem();
                else $editedRfqItem = RfQItem::find($item['id']);

                $editedRfqItem->fill($item);

                $editedRfqItem->rfq_id = $editedRfq->id;
                $editedRfqItem->type = strtoupper($item['type']);

                if ($item['type'] === 'Stock') {

                    $editedRfqItem->product_id = $item['item_id'];
                    // $editedRfqItem->project_id = $editedRfq->project_id;
                }
                else if ($item['type'] === 'Expense') {

                    $editedRfqItem->expense_account_id = $item['item_id'];
                    // $editedRfqItem->project_id = $editedRfq->project_id;
                }

                $editedRfqItem->quantity = $item['qty'];

                $editedRfqItem->save();
            }

            DB::commit();
        }
        catch (Exception $ex) {

            DB::rollBack();

            return [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ];


            return errorHandler('Error Updating Direct Purchase', $th);
        }


        return new RedirectResponse(route('biller.rfq.index'), ['flash_success' => 'RFQ updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteRfQRequest $request, $id)
    {
        //
    }
//    public function printRfQ($id)
//    {
//        $headers = [
//            "Content-type" => "application/pdf",
//            "Pragma" => "no-cache",
//            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
//            "Expires" => "0"
//        ];
//
//        // $data = BillDetailsTrait::bill_details($id);
//        $data = RfQ::find($id);
//
//        $html = view('focus.bill.print_rfq', $data)->render();
//        $pdf = new \Mpdf\Mpdf(config('pdf'));
//        $pdf->WriteHTML($html);
//
//        return Response::stream($pdf->Output('rfq.pdf', 'I'), 200, $headers);
//    }

    public function printRfq($rfqId, Request $request)
    {
        $rfq = RfQ::where('id' ,$rfqId)->with(['project', 'items'])->first();
        $company = Company::find(Auth::user()->ins);
        $supplier = Supplier::find($request->supplier_id);

        $html = view('focus.rfq.print_rfq', compact('rfq', 'company','supplier'))->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);

        $name = "RFQ-" . $rfq->tid . "  " . $rfq->subject;
        $name .= '.pdf';

        return Response::stream($pdf->Output($name, 'I'), 200, $this->headers);
    }

    public function approve(Request $request, $rfq_id)
    {
        try {
            $data = $request->only(['status','status_note']);
            $rfq = RfQ::find($rfq_id);
            if($data['status'] == 'approved' && $rfq->status != 'approved')
            {
                $data['approved_by'] = auth()->user()->id;
            }
            $rfq->update($data);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating RFQ status',$th);
        }
        return new RedirectResponse(route('biller.rfq.index'), ['flash_success' => 'RFQ Status updated successfully']);
    }

    public function rfq_generate($rfq_id, $supplier_id, $token)
    {
        $rfq = RFQ::withoutGlobalScopes()->where('id',$rfq_id)->first();
        $supplier = Supplier::withoutGlobalScopes()->find($supplier_id);
        $expected_token = hash('sha256', $rfq_id . env('APP_KEY'));
        if ($token !== $expected_token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access.'
            ], 403);
        }
        $company = Company::find($rfq->ins) ?: new Company;
        
        $html = view('focus.rfq.print_rfq', ['rfq' => $rfq, 'company' => $company, 'supplier' => $supplier])->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        $headers = array(
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        return Response::stream($pdf->Output('payment.pdf', 'I'), 200, $headers);
    }

    public function send_sms_and_email(Request $request, $rfq_id)
    {
        // dd($request->all());
        try {
            $data = $request->only(['send_email_sms']);
            $supplier_ids = $request->input('supplier_ids',[]);
            $suppliers = Supplier::whereIn('id', $supplier_ids)->get();
            $rfq = RFQ::find($rfq_id);
            foreach ($suppliers as $supplier){
                $this->send_payment_link($rfq, $data, $supplier);
            }
        } catch (\Throwable $th) {dd($th);
            //throw $th;
            return errorHandler('Error Sending Invoice Payment Receipt', $th);
        }
        return back()->with('flash_success','Sending Invoice Payment Successfully!!');
    }

    public function send_payment_link($rfq, $input, $supplier){
        if ($rfq) {
            $supplierName = @$supplier->company;
            $supplierEmail = $supplier['email'];
            //?? ;
            $supplierPhone = $supplier['phone'];
            //;
            $supplier_id = $supplier->id;
            
            // Find the company
            $company = Company::find(auth()->user()->ins);
            $companyName = "From " . Str::title($company->sms_email_name) . ":";
        
            // Initialize email and SMS data
            $emailInput = [
                'subject' => 'Request for Quotation (RFQ)',
                'mail_to' => $supplierEmail,
                'name' => $supplierName,
            ];
            
            $smsData = [
                'user_type' => 'supplier',
                'delivery_type' => 'now',
                'message_type' => 'single',
                'phone_numbers' => $supplierPhone,
                'sent_to_ids' => $supplier_id,
            ];
            $secureToken = hash('sha256', $rfq->id . env('APP_KEY'));
            $link = route('rfq_link', [
                'rfq_id' => $rfq->id,
                'supplier_id' => $supplier_id,
                'token' => $secureToken
            ]);
            // dd($link);
        
            // Handle each status case
            if ($rfq) {
                // For 
                $emailInput['text'] = "Dear $supplierName, I hope you are doing well. Please find attached our Request for Quotation (RFQ) for your review. Kindly provide us with a detailed quotation based on the specifications outlined in the attached document. : {$link}";
                $smsText = $companyName . " Dear $supplierName, I hope you are doing well. Please find attached our Request for Quotation (RFQ) for your review. Kindly provide us with a detailed quotation based on the specifications outlined in the attached document. : {$link}";
            } 
        
            // Only proceed 
            if (isset($smsText)) {
                if($input['send_email_sms'] == 'both' || $input['send_email_sms'] == 'sms'){
                    // Prepare SMS data
                    $smsData['subject'] = $smsText;
                    $cost_per_160 = 0.6;
                    $charCount = strlen($smsText);
                    $blocks = ceil($charCount / 160);
                    $smsData['characters'] = $charCount;
                    $smsData['cost'] = $cost_per_160;
                    $smsData['user_count'] = 1;
                    $smsData['total_cost'] = $cost_per_160*$blocks;
            
                    // Send SMS and email
                    $smsResult = SendSms::create($smsData);
                    (new RosesmsRepository(auth()->user()->ins))->textlocal($supplierPhone, $smsText, $smsResult);
                    $this->sendWhatsappMessageToSupplier($supplierPhone,$smsText);
                }
                
                if($input['send_email_sms'] == 'both' || $input['send_email_sms'] == 'email')
                {
                    $email = (new RosemailerRepository(auth()->user()->ins))->send($emailInput['text'], $emailInput);
                    $email_output = json_decode($email);
                    if ($email_output->status === "Success"){
    
                        $email_data = [
                            'text_email' => $emailInput['text'],
                            'subject' => $emailInput['subject'],
                            'user_emails' => $emailInput['mail_to'],
                            'user_ids' => $supplier_id,
                            'ins' => auth()->user()->ins,
                            'user_id' => auth()->user()->id,
                            'status' => 'sent'
                        ];
                        SendEmail::create($email_data);
                    }
                }
            }
        }
    }

    public function sendWhatsappMessageToSupplier($supplierPhone, $smsText)
    {
        // Remove non-digits
        $cleanedNumber = preg_replace('/\D/', '', $supplierPhone);

        // Match allowed formats: 07XXXXXXXX, 01XXXXXXXX, 2547XXXXXXXX, 2541XXXXXXXX
        $pattern = '/^(0[17]\d{8}|254[17]\d{8})$/';

        if (preg_match($pattern, $cleanedNumber)) {
            if (preg_match('/^0[17]\d{8}$/', $cleanedNumber)) {
                // Convert 07/01 format to 2547/2541
                $phone_number = '254' . substr($cleanedNumber, 1);
            } else {
                // Already in correct 254 format
                $phone_number = $cleanedNumber;
            }

            // Prepare request data
            $new_request_data = [
                'fb_id' => $phone_number,
                'user_type' => 'whatsapp',
                'message' => $smsText,
            ];

            // Send message
            $new_request = new Request($new_request_data);
            $omni = new OmniController;
            return $omni->sendUserMessage($new_request);
        }

        return false; // Invalid number format
    }

    public function get_items(Request $request)
    {
        $rfq = RFQ::where('id', $request->rfq_id)->first();

        $pr_items = [];
        $exempted_count = 0;

        foreach ($rfq->items as $item) {
            // Check if the product has stock_type 'generic'
            if (@$item->product->product->stock_type === 'generic' || @$item->product->product->stock_type === 'service') {
                $exempted_count++;
                continue;
            }
            $item['project_name'] = '';
            if ($item->project){
                $quote_tid = !$item->project->quote ?: gen4tid('QT-', $item->project->quote->tid);
                $customer = !$item->project->customer ?: $item->project->customer->company;
                $branch = !$item->project->branch ?: $item->project->branch->name;
                $project_tid = gen4tid('PRJ-', $item->project->tid);
                $project = $item->project->name;
                $customer_branch = "{$customer}" .'-'. "{$branch}";
                $item['project_name'] = "[" . $quote_tid ."]"." - " . $customer_branch. " - ".$project_tid." - ".$project;
            }

            $pr_items[] = [
                'product_name' => $item->description,
                'product_id' => $item->product_id,
                'product_code' => @$item->product->code,
                'project_id' => $item->project_id ?? 0,
                'project_name' => $item['project_name'],
                'project_milestone_id' => $item->project_milestone_id ?? 0,
                'qty' => $item->quantity,
                'uom' => $item->uom,
                'price' => +$item->price > 0 ? $item->price : fifoCost($item->product_id),
            ];
        }

        return response()->json([
            'items' => $pr_items,
            'exempted_generic_count' => $exempted_count,
        ]);
    }

}
