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

namespace App\Http\Controllers\Focus\purchaseorder;


use App\Models\PurchaseClassBudgets\PurchaseClassBudget;
use App\Models\purchaseorder\Purchaseorder;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\purchaseorder\EditResponse;
use App\Repositories\Focus\purchaseorder\PurchaseorderRepository;

use App\Http\Requests\Focus\purchaseorder\StorePurchaseorderRequest;
use Illuminate\Http\Request;
use App\Http\Responses\Focus\purchaseorder\CreateResponse;
use App\Http\Responses\RedirectResponse;
use App\Models\additional\Additional;
use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use App\Models\hrm\Hrm;
use App\Models\items\PurchaseorderItem;
use App\Models\purchaseClass\PurchaseClass;
use App\Models\purchaseorder\PurchaseorderApproval;
use App\Models\purchaseorder\PurchaseorderReview;
use App\Models\purchaseorder\PurchaseorderReviewDoc;
use App\Models\purchaseorder\PurchaseorderReviewItem;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\supplier\Supplier;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use Closure;
use DateTime;
use DB;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Storage;
use Yajra\DataTables\Facades\DataTables;

/**
 * PurchaseordersController
 */
class PurchaseordersController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseorderRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param PurchaseorderRepository $repository ;
     */
    public function __construct(PurchaseorderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\purchaseorder\ManagePurchaseorderRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        $suppliers = Supplier::whereHas('purchase_orders')->get(['id', 'name']);

        return new ViewResponse('focus.purchaseorders.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatePurchaseorderRequestNamespace $request
     * @return \App\Http\Responses\Focus\purchaseorder\CreateResponse
     */
    public function create(StorePurchaseorderRequest $request)
    {
        return new CreateResponse('focus.purchaseorders.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInvoiceRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StorePurchaseorderRequest $request)
    {
        $request->validate([
            'currency_id' => 'required',
            'fx_curr_rate' => 'required',
        ]);

        $order = $request->only([
            'currency_id', 'fx_curr_rate', 'supplier_id', 'tid', 'date', 'due_date', 'term_id', 'project_id', 'note', 'tax',
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'project_milestone', 'purchaseClass',
            'requisition_type','rfq_id','purchase_requisition_id','approval_note'
        ]); 
        $order_items = $request->only([
            'item_id', 'description', 'uom', 'itemproject_id', 'qty', 'rate', 'taxrate', 'itemtax', 'amount', 'type','product_code','warehouse_id', 'item_purchase_class',
            'purchase_class_budget', 'product_id', 'supplier_product_id','import_request_id','milestone_id'
        ]);

        $user_ids = implode(',',$request->input('user_ids',[]));
        $order['user_ids'] = $user_ids;

        if (!empty($order['purchaseClass'])) {

            $purchaseDate = (new DateTime($order['date']))->format('Y-m-d');

            $pcBudget = PurchaseClassBudget::where('purchase_class_id', $order['purchaseClass'])
                ->whereHas('financialYear', function ($query) use ($purchaseDate) {
                    $query->whereDate('start_date', '<=', $purchaseDate)
                        ->whereDate('end_date', '>=', $purchaseDate);
                })
                ->first();
            $request->validate(['nullable',
                'purchaseClass' => [
                    function (string $attribute, $value, Closure $fail) use ($pcBudget) {
                        if (!$pcBudget) {
                            $fail("The selected Non-Project Class has no Budget for the year wherein lies the purchase date...");
                        }
                    },
                ],
            ]);
            $order = array_merge($order, ['purchase_class_budget' => $pcBudget->id]);
            unset($order['purchaseClass']);
        }
        else {
            $order = array_merge($order, ['purchase_class_budget' => null]);
            unset($order['purchaseClass']);
        }
        
        for ($u = 0; $u < count($order_items['purchase_class_budget']); $u++){
            if (!empty($order_items['purchase_class_budget'][$u])) {
                $purchaseDate = (new DateTime($order['date']))->format('Y-m-d');
                $pcBudget = PurchaseClassBudget::where('purchase_class_id', $order_items['purchase_class_budget'][$u])
                    ->whereHas('financialYear', function ($query) use ($purchaseDate) {
                        $query->whereDate('start_date', '<=', $purchaseDate)
                            ->whereDate('end_date', '>=', $purchaseDate);
                    })
                    ->first();
                $request->validate([
                    'purchaseClass' => ['nullable',
                        function (string $attribute, $value, Closure $fail) use ($pcBudget, $u) {
                            if (!$pcBudget) {
                                $fail("The selected Non-Project Class for item " . ($u+1) . " has no Budget for the year wherein lies the purchase date...");
                            }
                        },
                    ],
                ]);
                $order_items['purchase_class_budget'][$u] = $pcBudget->id;
            }
            else {
                $order_items['purchase_class_budget'][$u] = null;
            }
        }


        if (@$$order['project']) $order['purchase_class_budget'] = '';
        if (@$order['itemproject_id']) $order['item_purchase_class'] = '';

        $order['ins'] = auth()->user()->ins;
        $order['user_id'] = auth()->user()->id;
        $order_items = modify_array($order_items);
        $order_items = array_filter($order_items, fn($v) =>  $v['item_id']);

        try {
            $result = $this->repository->create(compact('order', 'order_items'));
        } catch (Exception $e) {
            return errorHandler('Error Creating Purchase Order', $e);
        }
        
        return new RedirectResponse(route('biller.purchaseorders.index'), ['flash_success' => 'Purchase Order Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @param EditPurchaseorderRequestNamespace $request
     * @return \App\Http\Responses\Focus\purchaseorder\EditResponse
     */
    public function edit(Purchaseorder $purchaseorder)
    {
        return new EditResponse($purchaseorder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StorePurchaseorderRequest $request, Purchaseorder $purchaseorder)
    {
        if ($request->exists('closure_status')) {
            $purchaseorder->update($request->only('closure_status', 'closure_reason'));
            return redirect()->back()->with('flash_success', 'Closure Status Updated Successfully');
        }

        $request->validate([
            'currency_id' => 'required',
            'fx_curr_rate' => 'required',
        ]);
        
        $order = $request->only([
            'currency_id', 'fx_curr_rate', 'supplier_id', 'tid', 'date', 'due_date', 'term_id', 'project_id', 'note', 'tax',
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'project_milestone', 'purchaseClass',
            'requisition_type','rfq_id','purchase_requisition_id','approval_note'
        ]);
        $order_items = $request->only([
            'id', 'item_id', 'description', 'uom', 'itemproject_id', 'qty', 'rate', 'taxrate', 'itemtax', 'amount', 'type','product_code','warehouse_id',
            'purchase_class_budget', 'product_id', 'supplier_product_id','import_request_id','milestone_id'
        ]);

        $user_ids = implode(',',$request->input('user_ids',[]));
        $order['user_ids'] = $user_ids;

        if (!empty($order['purchaseClass'])) {

            $purchaseDate = (new DateTime($order['date']))->format('Y-m-d');

            $pcBudget = PurchaseClassBudget::where('purchase_class_id', $order['purchaseClass'])
                ->whereHas('financialYear', function ($query) use ($purchaseDate) {
                    $query->whereDate('start_date', '<=', $purchaseDate)
                        ->whereDate('end_date', '>=', $purchaseDate);
                })
                ->first();
            $request->validate(['nullable',
                'purchaseClass' => [
                    function (string $attribute, $value, Closure $fail) use ($pcBudget) {
                        if (!$pcBudget) {
                            $fail("The selected Non-Project Class has no Budget for the year wherein lies the purchase date...");
                        }
                    },
                ],
            ]);
            $order = array_merge($order, ['purchase_class_budget' => $pcBudget->id]);
            unset($order['purchaseClass']);
        }
        else {
            $order = array_merge($order, ['purchase_class_budget' => null]);
            unset($order['purchaseClass']);
        }
        
        for ($u = 0; $u < count($order_items['purchase_class_budget']); $u++){
            if (!empty($order_items['purchase_class_budget'][$u])) {
                $purchaseDate = (new DateTime($order['date']))->format('Y-m-d');
                $pcBudget = PurchaseClassBudget::where('purchase_class_id', $order_items['purchase_class_budget'][$u])
                    ->whereHas('financialYear', function ($query) use ($purchaseDate) {
                        $query->whereDate('start_date', '<=', $purchaseDate)
                            ->whereDate('end_date', '>=', $purchaseDate);
                    })
                    ->first();
                $request->validate([
                    'purchaseClass' => ['nullable',
                        function (string $attribute, $value, Closure $fail) use ($pcBudget, $u) {
                            if (!$pcBudget) {
                                $fail("The selected Non-Project Class for item " . ($u+1) . " has no Budget for the year wherein lies the purchase date...");
                            }
                        },
                    ],
                ]);
                $order_items['purchase_class_budget'][$u] = $pcBudget->id;
            }
            else {
                $order_items['purchase_class_budget'][$u] = null;
            }
        }

        $order['ins'] = auth()->user()->ins;
        $order['user_id'] = auth()->user()->id;
        $order_items = modify_array($order_items);
        $order_items = array_filter($order_items, function ($val) { return $val['item_id']; });

        try {
            $result = $this->repository->update($purchaseorder, compact('order', 'order_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Purchase Order', $th);
        }

        return new RedirectResponse(route('biller.purchaseorders.index'), ['flash_success' => 'Purchase Order updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Purchaseorder $purchaseorder)
    {
        try {
            $this->repository->delete($purchaseorder);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Purchase Order', $th);
        }

        return new RedirectResponse(route('biller.purchaseorders.index'), ['flash_success' => 'Purchase Order deleted successfully']);        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Purchaseorder $purchaseorder)
    {   
        return new ViewResponse('focus.purchaseorders.view', compact('purchaseorder'));
    }

    /**
     * Purchase Order Goods
     */
    public function goods(Request $request)
    {
        $purchaseorder = Purchaseorder::find(request('purchaseorder_id'));
        $stock_goods = $purchaseorder? $purchaseorder->goods()->where('type', 'Stock')->get() : collect();
        $stock_goods = $stock_goods->map(function($v) {
            if ($v->productvariation) {
                $v->description .= " - {$v->productvariation->code}";
                $v->product_code = $v->productvariation->code;
                $v->stock_type = $v->productvariation->product ? $v->productvariation->product->stock_type : '';
            }
            if ($v->project){
                $quote_tid = !$v->project->quote ?: gen4tid('QT-', $v->project->quote->tid);
                $customer = !$v->project->customer ?: $v->project->customer->company;
                $branch = !$v->project->branch ?: $v->project->branch->name;
                $project_tid = gen4tid('PRJ-', $v->project->tid);
                $project = $v->project->name;
                $customer_branch = "{$customer}" .'-'. "{$branch}";
                //
                $v['project_tid'] = "[" . $quote_tid ."]"." - " . $customer_branch. " - ".$project_tid." - ".$project;
            }else{
                $v->project_tid = '';
            }
             $v->project_id = Purchaseorder::find($v->purchaseorder_id)->project->id ?? '';
             $v->project_name= Purchaseorder::find($v->purchaseorder_id)->project->name ?? '';
            return $v;
        });

        return response()->json($stock_goods);
    }

    public function send_single_sms(Request $request)
    {
        try {
            DB::beginTransaction();
            $company = Company::find(auth()->user()->ins);
            $pdfLink = $this->getPdfLink($request->id);
            $purchase_order = Purchaseorder::find($request->id);
            $text = 'From '.$company->cname. ': '.$request->subject .' ' .$pdfLink;
            $cost_per_160 = 0.6;
            $charCount = strlen($text);
            $phone = $request->sms_to;
            $data = [
                'subject' =>$text,
                'user_type' =>'supplier',
                'delivery_type' => 'now',
                'message_type' => 'single',
                'phone_numbers' => $phone,
                'sent_to_ids' => $purchase_order->supplier_id,
                'characters' => $charCount,
                'cost' => $cost_per_160,
                'user_count' => 1,
                'total_cost' => $cost_per_160*1,
    
            ];
            $result = SendSms::create($data);
            $sms = (new RosesmsRepository(auth()->user()->ins))->textlocal($phone, $text, $result);
            $sms_response = $sms->getData(true);
            if($sms_response['status'] == 'success'){
                DB::commit();
                return redirect()->back()->with('flash_success', 'Message Sent Successfully');
            }else if($sms_response['status'] == 'error'){
                return redirect()->back()->with('flash_error', $sms_response['message']);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler("Error sending Sms ".$th->getMessage(), $th);
        }
        return redirect()->back()->with('flash_success', 'Message Sent Successfully');
    }

    public function generate_po_pdf($purchase_orderId)
    {
        // Fetch the purchase_order details
        $resource = Purchaseorder::find($purchase_orderId);
        $company = Company::find(auth()->user()->ins);

        $html = view('focus.bill.print_purchaseorder', [
            'resource' => $resource,
            'company' => $company
        ])->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);


        // Define the PDF file path
        $timestamp = time();
        $relativePath = "purchase_orders/purchase_order_{$purchase_orderId}_{$timestamp}.pdf";
        $pdfFilePath = storage_path("app/public/{$relativePath}");
        $file_path = public_path() . '/storage/' . $relativePath;

        // Save the PDF file to the server
        $pdf->Output($pdfFilePath, \Mpdf\Output\Destination::FILE);
        $pdf->Output($file_path, \Mpdf\Output\Destination::FILE);

        // Return the path to the generated PDF
        return $relativePath;
    }

    public function getPdfLink($purchase_orderId)
    {
        $pdfFilePath = $this->generate_po_pdf($purchase_orderId);

            // Generate the relative URL to the PDF
        $relativeUrl = Storage::url($pdfFilePath);
        

        // Define your domain name
        $domainName = config('app.url'); // or you can hardcode the domain name

        // Construct the full URL
        $fullUrl = $domainName . '/' . ltrim($relativeUrl, '/');

        return $fullUrl;
    }

    public function change_status(Request $request, $purchase_order_id)
    {
        $data = $request->only(['approval_status', 'approved_date','status_note']);
        $data['user_id'] = auth()->user()->id;
        $data['purchase_order_id'] = $purchase_order_id;
        $data['ins'] = auth()->user()->ins;
        $data['approved_date'] = date_for_database($data['approved_date']);
        try {
            if($data['approval_status'] == 'approved'){
                $data['approved_by'] = auth()->user()->id;
            }
            $result = PurchaseorderApproval::create($data);
            if($result){
                $po = Purchaseorder::find($purchase_order_id);
                $user = Hrm::find($po->user_id);
                $reviewers = explode(',',$po->user_ids);
                if(count($reviewers) > 0){

                    $this->user_notify($user, $po, $result);
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Approval Status', $th);
        }
        return back()->with('flash_success', 'LPO Status Update Success!!');
    }
    public function update_status(Request $request)
    {
        $data = $request->only(['approval_status','status_note']);
        
        $state = PurchaseorderApproval::find($request->id);
        try {
            if($data['approval_status'] == 'approved' && $state->approval_status != 'approved'){
                $data['approved_by'] = auth()->user()->id;
                $data['approved_date'] = date_for_database($request['approved_date']);
            }
            $state->update($data);
            
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Approval Status', $th);
        }
        return back()->with('flash_success', 'LPO Status Update Success!!');
    }
    public function user_notify($user, $po, $input)
    {
        if ($user) {

            $pattern = '/^(0[17]\d{8}|254[17]\d{8})$/';

            if ($user->meta) {
                $cleanedNumber = preg_replace('/\D/', '', $user->meta->secondary_contact);
                
                if (preg_match($pattern, $cleanedNumber)) {
                    // Convert 01xxxxxxxx to 2541xxxxxxxx format
                    if (preg_match('/^01\d{8}$/', $cleanedNumber)) {
                        $phone_number = '254' . substr($cleanedNumber, 1); // Replace '0' with '254'
                    } else {
                        $phone_number = $cleanedNumber;
                    }

                    $user_id = $user->id;
                }
            }

            $userName = @$user->fullname;
            $userEmail = $user['personal_email'];
            $userPhone = $phone_number;
            $user_id = $user->id;
            
            // Find the company
            $company = Company::find(auth()->user()->ins);
            $setting = RecipientSetting::where('type', 'lpo_notification')->where('ins', $company->id)->first();
            $companyName = "From " . Str::title($company->sms_email_name) . ":";
            $po_no = gen4tid('PO-',$po->tid);
        
            // Initialize email and SMS data
            $emailInput = [
                'subject' => "LPO Approval",
                'mail_to' => $userEmail,
                'name' => $userName,
            ];
            
            $smsData = [
                'user_type' => 'employee',
                'delivery_type' => 'now',
                'message_type' => 'single',
                'phone_numbers' => $userPhone,
                'sent_to_ids' => $user_id,
            ];
            
        
            // Handle each status case
            if ($user) {
                // For 
                $status = ucfirst($input['approval_status']);
                $formattedStatus = $status === 'amend' ? 'returned for amendment' : strtolower($status);
                
                $emailInput['text'] = "Dear $userName, 
                We would like to inform you that the LPO, {$po_no} has been {$formattedStatus} ";
                
                $message = "{$companyName} - Dear $userName, LPO {$po_no} has been {$formattedStatus}.\n\n";
                $smsText = $message;
            } 
        
            // Only proceed 
            if (isset($smsText)) {
                if($setting->sms == 'yes')
                {
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
                    (new RosesmsRepository(auth()->user()->ins))->textlocal($userPhone, $smsText, $smsResult);
                }

                if($setting->email == 'yes')
                {
                    $email = (new RosemailerRepository(auth()->user()->ins))->send($emailInput['text'], $emailInput);
                    $email_output = json_decode($email);
                    if ($email_output->status === "Success"){

                        $email_data = [
                            'text_email' => $emailInput['text'],
                            'subject' => $emailInput['subject'],
                            'user_emails' => $emailInput['mail_to'],
                            'user_ids' => $user_id,
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

    public function create_lpo_review($po_id)
    {
        $po = Purchaseorder::find($po_id);
        $additionals = Additional::all();
        $warehouses = Warehouse::all();
        $purchaseClasses = PurchaseClass::whereHas('budgets', fn ($q) => $q->where('budget', '>', 0))
            ->whereHas('budgets.financialYear')
            ->select('id', 'name')
            ->get();
        return view('focus.purchaseorders.create_lpo_review', compact('po','additionals','warehouses','purchaseClasses'));
    }
    public function lpo_review_comment(Request $request, $po_id)
    {
        // dd($request->all());
        $order = $request->only([
            'stock_grandttl','stock_subttl','stock_tax',
            'grandtax', 'grandttl', 'paidttl',
        ]);
        
        $order_items = $request->only([
            'id', 'item_id', 'qty', 'amount'
            , 'product_id'
        ]);
        $document_data = $request->only('caption','file_name');
        $data = $request->only(['review_date','general_comment']);
        $data['purchase_order_id'] = $po_id;
        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        $data_items = $request->only([
            'id', 'item_id', 'qty', 'amount'
            , 'product_id'
        ]);
        $order_items = modify_array($order_items);
        $order_items = array_filter($order_items, function ($val) { return $val['item_id']; });
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($val) { return $val['item_id']; });
        $document_data = modify_array($document_data);
        $purchaseorder = PurchaseOrder::find($po_id);
        // dd($order_items, $order);
        try {
            DB::beginTransaction();
            foreach ($order as $key => $val) {
                $rate_keys = [
                    'stock_subttl', 'stock_tax', 'stock_grandttl', 'grandtax', 'grandttl', 'paidttl', 'fx_curr_rate',
                ];    
                if (in_array($key, $rate_keys, 1)) 
                    $order[$key] = numberClean($val);
            }
            foreach ($data as $key => $val) {    
                if (in_array($key, ['review_date'], 1)) 
                    $data[$key] = date_for_database($val);
            }
            $purchaseorder->update($order);
            $result = PurchaseorderReview::create($data);
            // $result = $purchaseorder;
            
            // update or create new items
            foreach ($order_items as $item) {
                $item = array_replace($item, [
                    'qty' => numberClean($item['qty']),
                    'amount' => numberClean($item['amount']),
                ]);
                // dd($item);
                $order_item = PurchaseorderItem::firstOrNew(['id' => $item['id']]);
                $order_item->fill($item);
                if (!$order_item->id) unset($order_item->id);
                $order_item->save();                
            }
            $data_items = array_map(function ($v) use($result) {
                $id = $v['id'];
                unset($v['id']);
                return array_replace($v, [
                    'ins' => $result->ins,
                    'user_id' => $result->user_id,
                    'purchase_order_item_id' => $id,
                    'purchase_order_review_id' => $result->id,
                    'qty' => numberClean($v['qty']),
                    'amount' => numberClean($v['amount'])
                ]);
            }, $data_items);
            PurchaseorderReviewItem::insert($data_items);

            $document_data = array_map(function ($v) use($result) {
                return [
                    'purchase_order_review_id' => $result->id,
                    'caption' => $v['caption'],
                    'file_name' => $this->uploadFile($v['file_name']),
                    'ins' => auth()->user()->ins,
                    'user_id' => auth()->user()->id,
                ];
            }, $document_data);
            PurchaseorderReviewDoc::insert($document_data);
            if($result)
            {
                DB::commit();
            }
        } catch (\Throwable $th) {dd($th);
            DB::rollBack();
            return errorHandler('Error Creating a Review Comment', $th);
        }
        return back()->with('flash_success','Review Comment Created Successfully!!');
    }

    public function index_review()
    {
        return view('focus.purchaseorders.index_review');
    }

    public function get_lpo_reviews()
    {
        $reviews = PurchaseorderReview::get();
        return DataTables::of($reviews)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($review) {
                 return gen4tid('REV-',$review->tid);
            })
            ->addColumn('general_comment', function ($review) {
                 return $review->general_comment;
            })
            ->addColumn('supplier', function ($review) {
                 return $review->purchaseorder->supplier ? $review->purchaseorder->supplier->company : '';
            })
            ->addColumn('review_date', function ($review) {
                 return dateFormat($review->review_date);
            })
            ->addColumn('lpo_no', function ($review) {
                return @$review->purchaseorder ? gen4tid('PO-',$review->purchaseorder->tid) : '';
           })
            ->addColumn('subject', function ($review) {
                return @$review->purchaseorder ? $review->purchaseorder->subject : '';
           })
            ->addColumn('total', function ($review) {
                return @$review->purchaseorder ? numberFormat($review->purchaseorder->grandttl) : '';
           })
            ->addColumn('actions', function ($review) {
                $btn = '<a href="'.route('biller.purchaseorders.show_lpo_review', [$review]).'" class="btn btn-secondary round" data-toggle="tooltip" data-placement="top" title="View">
                <i class="fa fa-eye" aria-hidden="true"></i></a> ';
                return $btn;
            })
            ->make(true);
    }

    public function show_lpo_review($review_id)
    {
        $lpo_review = PurchaseorderReview::find($review_id);
        return view('focus.purchaseorders.show_lpo_review', compact('lpo_review'));
    }

    public function uploadFile($file)
    {
        $file_name = time() . $file->getClientOriginalName();
        $file_path = 'img' . DIRECTORY_SEPARATOR . 'pm_documents' . DIRECTORY_SEPARATOR;
        $storage = Storage::disk('public');

        $storage->put($file_path . $file_name, file_get_contents($file->getRealPath()));

        return $file_name;
    }
}
