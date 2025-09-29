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
namespace App\Http\Controllers\Focus\rfq_analysis;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Company\Company;
use App\Models\rfq\RfQ;
use App\Models\rfq\RfQItem;
use App\Models\rfq_analysis\RfQAnalysis;
use App\Models\rfq_analysis\RfQAnalysisDetail;
use App\Models\rfq_analysis\RfQAnalysisItem;
use App\Models\rfq_analysis\RfQAnalysisSupplierItem;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\supplier\Supplier;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use App\Repositories\Focus\rfq_analysis\RfQAnalysisRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * RfQAnalysisController
 */
class RfQAnalysisController extends Controller
{
    /**
     * variable to store the repository object
     * @var RfQAnalysisRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param RfQAnalysisRepository $repository ;
     */
    public function __construct(RfQAnalysisRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\RfQAnalysis\
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.rfq_analysis.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateRfQAnalysisRequestNamespace $request
     * @return \App\Http\Responses\Focus\RfQAnalysis\CreateResponse
     */
    public function create(Request $request)
    {
        return view('focus.rfq_analysis.create');
    }
    public function create_analysis($rfq_id)
    {
        $rfq = RfQ::find($rfq_id);
        $supplier_ids = explode(',', $rfq->supplier_ids);
        $suppliers = Supplier::whereIn('id', $supplier_ids)->get();
        return view('focus.rfq_analysis.create', compact('suppliers', 'rfq'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        $winner_suppler = Supplier::where('company',$input['winner_supplier_name'])->first();
        // dd($input);
        $winner_supplier_id = $winner_suppler->id;
        return DB::transaction(function () use ($request, $winner_supplier_id) {
            // Insert RFQ
            $rfq_analysis = RfQAnalysis::create([
                'subject' => $request['subject'],
                'rfq_id' => $request['rfq_id'],
                'date' => date_for_database($request['date']),
                // 'availability_details' => $request['availability_details'],
                // 'credit_terms' => $request['credit_terms'],
                // 'comment' => $request['comment'],
            ]);

            foreach ($request['others'] as $supplier_id => $details) {
                RfQAnalysisDetail::create([
                    'rfq_analysis_id' => $rfq_analysis->id,
                    'supplier_id' => $supplier_id,
                    'availability_details' => $details['availability_details'][0] ?? null,
                    'credit_terms' => $details['credit_terms'][0] ?? null,
                    'comment' => $details['comment'][0] ?? null,
                ]);
            }
        
            // Insert RFQ Items and store references for lookup
            $rfqItems = [];
            foreach ($request['product_id'] as $index => $product_id) {
                // dd($product_id);
                $supplier = Supplier::where('company',$request['supplier_id'][$index])->first();
                $rfqItems[$product_id] = RfQAnalysisItem::create([
                    'rfq_analysis_id' => $rfq_analysis->id,
                    'product_id' => $product_id,
                    'rfq_item_id' => $request['rfq_item_id'][$index],
                    'supplier_id' => $supplier->id,
                ]);
            }
        
            // Insert RFQ Suppliers
            foreach ($request['supplier'] as $supplier_id => $products) {
                foreach ($products as $product_id => $details) {
                    RfQAnalysisSupplierItem::create([
                        'rfq_analysis_id' => $rfq_analysis->id,
                        'supplier_id' => $supplier_id,
                        'product_id' => $product_id,
                        'rfq_item_id' => $rfqItems[$product_id]->rfq_item_id, // Fetch corresponding rfq_item_id
                        'amount' => $details['amount'][0] ?? null,
                        'price' => $details['price'][0] ?? null,
                    ]);
                }
            }
            $this->winner_supplier($rfq_analysis->id, $winner_supplier_id);
            return new RedirectResponse(route('biller.rfq_analysis.edit',$rfq_analysis), ['flash_success' => 'RFQ Analysis Created Successfully!!']);

        });
        
    }

    public function winner_supplier($rfq_analysis_id, $supplier_id)
    {
        
        //Get prices for selected supplier
        $data = [
            'supplier_id' => $supplier_id
        ];
        $rfq_supplier_items = RfQAnalysisSupplierItem::where('supplier_id',$supplier_id)->where('rfq_analysis_id', $rfq_analysis_id)->get();
        foreach($rfq_supplier_items as $item)
        {
            $rfq_item = RfQItem::where('id', $item['rfq_item_id'])->first();
            $rfq_item->supplier_id = $supplier_id;
            $rfq_item->price = $item->price;
            $rfq_item->update();
        }
        $rfq_analysis = RfQAnalysis::find($rfq_analysis_id);
        $rfq_analysis->update($data);

        return $rfq_analysis;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\RfQAnalysis\RfQAnalysis $RfQAnalysis
     * @param EditRfQAnalysisRequestNamespace $request
     * @return \App\Http\Responses\Focus\RfQAnalysis\EditResponse
     */
    public function edit($rfq_analysis_id, Request $request)
    {
        $rfq_analysis = RfQAnalysis::find($rfq_analysis_id);
        // dd($rfq_analysis);
        $rfq = RfQ::find($rfq_analysis->rfq_id);
        $supplier_ids = explode(',', $rfq->supplier_ids);
        $suppliers = Supplier::whereIn('id', $supplier_ids)->get();
        return view('focus.rfq_analysis.edit', compact('rfq_analysis', 'suppliers','rfq'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRfQAnalysisRequestNamespace $request
     * @param App\Models\RfQAnalysis\RfQAnalysis $RfQAnalysis
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, $rfq_analysis_id)
    {
        $input = $request->except(['_token', 'ins']);
        // dd($input);
        $winner_suppler = Supplier::where('company',$input['winner_supplier_name'])->first();
        $winner_supplier_id = $winner_suppler->id;
        DB::transaction(function () use ($request, $rfq_analysis_id, $winner_supplier_id) {
            // Find and update RFQ Analysis
            $rfq_analysis = RfQAnalysis::updateOrCreate(
                ['id' => $rfq_analysis_id], 
                [
                    'subject' => $request['subject'],
                    'date' => date_for_database($request['date']),
                    'availability_details' => $request['availability_details'],
                    'credit_terms' => $request['credit_terms'],
                    'comment' => $request['comment'],
                ]
            );

            foreach ($request['others'] as $supplier_id => $details) {
                RfQAnalysisDetail::updateOrCreate(
                    [
                        'rfq_analysis_id' => $rfq_analysis->id,
                        'supplier_id' => $supplier_id,
                    ],
                    [
                        'availability_details' => $details['availability_details'][0] ?? null,
                        'credit_terms' => $details['credit_terms'][0] ?? null,
                        'comment' => $details['comment'][0] ?? null,
                    ]
                );
            }
            
        
            // Update RFQ Items using rfq_analysis_item_id
            $rfqItems = [];
            foreach ($request['product_id'] as $index => $product_id) {
                $supplier = Supplier::where('company',$request['supplier_id'][$index])->first();
                $rfqItems[$product_id] = RfQAnalysisItem::updateOrCreate(
                    [
                        'id' => $request['rfq_analysis_item_id'][$index] ?? null, // Update if ID exists
                    ],
                    [
                        'rfq_analysis_id' => $rfq_analysis->id,
                        'product_id' => $product_id,
                        'rfq_item_id' => $request['rfq_item_id'][$index],
                        'supplier_id' => $supplier->id,
                        // 'availability_details' => $request['availability_details'][$index],
                        // 'credit_terms' => $request['credit_terms'][$index],
                        // 'comment' => $request['comment'][$index],
                    ]
                );
            }
        
            // Update RFQ Suppliers using existing IDs
            foreach ($request['supplier'] as $supplier_id => $products) {
                foreach ($products as $product_id => $details) {
                    RfQAnalysisSupplierItem::updateOrCreate(
                        [
                            'id' => $details['id'][0] ?? null, // Corrected access to 'id'
                        ],
                        [
                            'rfq_analysis_id' => $rfq_analysis->id,
                            'supplier_id' => $supplier_id,
                            'product_id' => $product_id,
                            'rfq_item_id' => $rfqItems[$product_id]->rfq_item_id, // Ensure it references the updated RFQ item
                            'price' => $details['price'][0] ?? null,
                            'amount' => $details['amount'][0] ?? null,
                        ]
                    );
                }
            }
            $this->winner_supplier($rfq_analysis->id, $winner_supplier_id);
        });
        
        //Update the model using repository update method
        // $this->repository->update($rfq_analysis, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.rfq_analysis.index'), ['flash_success' => 'RFQ Analysis Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteRfQAnalysisRequestNamespace $request
     * @param App\Models\RfQAnalysis\RfQAnalysis $RfQAnalysis
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy($rfq_analysis_id, Request $request)
    {
        $rfq_analysis = RfQAnalysis::find($rfq_analysis_id);
        //Calling the delete method on repository
        $this->repository->delete($rfq_analysis);
        //returning with successfull message
        return new RedirectResponse(route('biller.rfq_analysis.index'), ['flash_success' => 'RFQ Analysis Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteRfQAnalysisRequestNamespace $request
     * @param App\Models\RfQAnalysis\RfQAnalysis $RfQAnalysis
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show($rfq_analysis_id)
    {

        $rfq_analysis = RfQAnalysis::find($rfq_analysis_id);
        // dd($rfq_analysis);
        $rfq = RfQ::find($rfq_analysis->rfq_id);
        $supplier_ids = explode(',', $rfq->supplier_ids);
        $suppliers = Supplier::whereIn('id', $supplier_ids)->get();
        //returning with successfull message
        return new ViewResponse('focus.rfq_analysis.view', compact('rfq_analysis', 'suppliers','rfq'));
    }

    public function select_supplier(Request $request, $rfq_analysis_id)
    {
        //Get prices for selected supplier
        try {
            DB::beginTransaction();
            $data = $request->only(['supplier_id', 'remark']);
            $rfq_supplier_items = RfQAnalysisSupplierItem::where('supplier_id',$data['supplier_id'])->where('rfq_analysis_id', $rfq_analysis_id)->get();
            // dd($rfq_supplier_items);
            foreach($rfq_supplier_items as $item)
            {
                $rfq_item = RfQItem::where('id', $item['rfq_item_id'])->first();
                $rfq_item->supplier_id = $data['supplier_id'];
                $rfq_item->price = $item->price;
                $rfq_item->update();
            }
            $rfq_analysis = RfQAnalysis::find($rfq_analysis_id);
            $rfq_analysis->update($data);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
            return errorHandler('Error updating rfq_analysis supplier', $th);
        }
        
        
        return back()->with('flash_success', 'RFQ Analysis Suppler Selected Successfully!!');
    }

    public function approve(Request $request, $rfq_analysis_id)
    {
        $data = $request->only(['status', 'approved_date','status_note']);
        $data['approved_date'] = date_for_database($data['approved_date']);
        try {
            $rfq_analysis = RfQAnalysis::find($rfq_analysis_id);
            if($data['status'] == 'approved'){
                if(!$rfq_analysis->supplier_id) return back()->with('flash_error', 'Supplier is Not Selected!!');
                $data['approved_by'] = auth()->user()->id;
            }
            $rfq_analysis->update($data);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Approval Status', $th);
        }
        return back()->with('flash_success', 'RfQ Analysis Status Update Success!!');
    }

    public function notify_suppliers(Request $request, $rfq_analysis_id)
    {
        $data = $request->only(['send_email_sms', 'date','subjects','text_message']);
        $data['subject'] = $data['subjects'];
        $data['rfq_analysis_id'] = $rfq_analysis_id;
        unset($data['subjects']);
        $data['date'] = date_for_database($data['date']);
        try {
            $data['ins'] = auth()->user()->ins;
            $data['user_id'] = auth()->user()->id;
            $data['created_at'] = now();
            $data['updated_at'] = now();
            foreach($request['supplier_ids'] as $supplier_id)
            {
                $supplier = Supplier::find($supplier_id);
                $this->send_payment_link($supplier, $data);
                $data['supplier_ids'] = $supplier_id;
                $data['phone_number'] = $supplier->phone;
                $data['email'] = $supplier->email;
                
                DB::table('send_invoices')->insert($data);
            }
        } catch (\Throwable $th) {dd($th);
            //throw $th;
            return errorHandler('Error Updating Approval Status', $th);
        }
        return back()->with('flash_success', 'RfQ Analysis Status Update Success!!');
    }
    public function send_payment_link($supplier, $input){
        if ($supplier) {
            $supplier_name = @$supplier->company;
            $supplierEmail = $supplier->email;
            $supplierPhone = $supplier->phone;
            $supplier_id = $supplier->id;
            
            // Find the company
            $company = Company::find(auth()->user()->ins);
            $companyName = "From " . Str::title($company->sms_email_name) . ":";
        
            // Initialize email and SMS data
            $emailInput = [
                'subject' => $input['subject'],
                'mail_to' => $supplierEmail,
                'name' => $supplier_name,
            ];
            
            $smsData = [
                'user_type' => 'supplier',
                'delivery_type' => 'now',
                'message_type' => 'single',
                'phone_numbers' => $supplierPhone,
                'sent_to_ids' => $supplier_id,
            ];
        
            // Handle each status case
            if ($supplier) {
                // For 
                $emailInput['text'] = "Dear $supplier_name, ".$input['text_message'];
                $smsText = $companyName . " Dear $supplier_name, ".$input['text_message'];
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

}
