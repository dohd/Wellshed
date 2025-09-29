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

namespace App\Http\Controllers\Focus\purchase_request;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\User\User;
use App\Models\Company\Company;
use App\Models\Company\RecipientSetting;
use App\Models\hrm\Hrm;
use App\Models\part\Part;
use App\Models\purchase_request\PurchaseRequest;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\stock_transfer\StockTransfer;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use App\Repositories\Focus\purchase_request\PurchaseRequestRepository;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseRequestsController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseRequestRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param PurchaseRequestRepository $repository ;
     */
    public function __construct(PurchaseRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.purchase_requests.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = PurchaseRequest::where('ins', auth()->user()->ins)->max('tid');
        $users = Hrm::all();
        $fg_goods = Part::all();

        return view('focus.purchase_requests.create', compact('users', 'tid','fg_goods'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->only(['tid','employee_id','date','priority','expect_date','note','project_id','project_milestone_id','item_type','part_id']);
        $reviewer_ids = implode(',',$request->input('reviewer_ids',[]));
        $data['reviewer_ids'] = $reviewer_ids;
        $data_items = $request->only(['product_id','product_name','unit_id','qty','price','milestone_item_id','budget_item_id','remark','part_item_id']);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['qty'] > 0 && $v['product_id']);
        try {
            $this->repository->create(compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Creating Requisition '.$th->getMessage(), $th);
        }

        return new RedirectResponse(route('biller.purchase_requests.index'), ['flash_success' => 'Purchase Requisition Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  PurchaseRequest $purchase_request
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseRequest $purchase_request)
    {
        $users = Hrm::all();
        $fg_goods = Part::all();

        return view('focus.purchase_requests.edit', compact('purchase_request', 'users','fg_goods'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  PurchaseRequest $purchase_request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseRequest $purchase_request)
    {
        $data = $request->only(['tid','employee_id','date','priority','expect_date','note','project_id','project_milestone_id','item_type','part_id']);
        $reviewer_ids = implode(',',$request->input('reviewer_ids',[]));
        $data['reviewer_ids'] = $reviewer_ids;
        $data_items = $request->only(['product_id','product_name','unit_id','qty','price','id','milestone_item_id','budget_item_id','remark','part_item_id']);
        $data_items = modify_array($data_items);
        try {
            $this->repository->update($purchase_request, compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Requisition '.$th->getMessage(), $th);
        }

        return new RedirectResponse(route('biller.purchase_requests.index'), ['flash_success' => 'Purchase Requisition Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  PurchaseRequest $purchase_request
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseRequest $purchase_request)
    {
        $this->repository->delete($purchase_request);

        return new RedirectResponse(route('biller.purchase_requests.index'), ['flash_success' => 'Purchase Requisition Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  PurchaseRequest $purchase_request
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseRequest $purchase_request)
    {
        return view('focus.purchase_requests.view', compact('purchase_request'));
    }

    public function approve(Request $request)
    {
        try {
            $purchase_request = PurchaseRequest::find($request->id);
            DB::beginTransaction();
            $data = $request->except(['_token','id']);
            if($data['status'] == 'approved' && $purchase_request->item_type == 'project')
            {
                foreach ($purchase_request->items as $key => $item) {
                    if($item->budget_item){
                        $total_budget_qty = $item->budget_item->qty_requested + $item->qty;
                        if($item->budget_item->new_qty < $total_budget_qty) throw ValidationException::withMessages(['Requested QTY Exceeds Budgeted!! Item'.$item->product_name]);
                        $item->budget_item->qty_requested += $item->qty;
                        $item->budget_item->update();
                    }
                    if($item->milestone_item){
                        $total_milestone_qty = $item->milestone_item->qty_requested + $item->qty;
                        if($item->milestone_item->qty < $total_milestone_qty) throw ValidationException::withMessages(['Requested QTY Exceeds Milestone!!']);
                        $item->milestone_item->qty_requested += $item->qty;
                        $item->milestone_item->update();
                    }
                    
                }
            }
            if($data['status'] == 'approved')
            {
                $data['approved_by_id'] = auth()->user()->id;
                $data['approved_date'] = date_for_database($data['approved_date']);
            }
            $purchase_request->update($data);
            if ($purchase_request){
                DB::commit();
                $hrm = Hrm::find($purchase_request->user_id);
                $reviewers = explode(',',$purchase_request->reviewer_ids);
                if(count($reviewers) > 0){
                    $this->user_notify($hrm, $purchase_request, $data);
                }
            }
            
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return errorHandler('Error Approving Requisition '.$th->getMessage(), $th);
        }
        return back()->with('flash_success','Requisition Approved Successfully !!');
    }

    public function user_notify($user, $mr, $input)
    {
        if (!$user || !$user->meta) return;
    
        $cleanedNumber = preg_replace('/\D/', '', $user->meta->secondary_contact);
        $pattern = '/^(0[17]\d{8}|254[17]\d{8})$/';
    
        if (!preg_match($pattern, $cleanedNumber)) return;
    
        // Convert to international format if needed
        $phone_number = preg_match('/^01\d{8}$/', $cleanedNumber)
            ? '254' . substr($cleanedNumber, 1)
            : $cleanedNumber;
    
        $user_id = $user->id;
        $userName = $user->fullname ?? 'User';
        $userEmail = $user->personal_email ?? null;
        $company = Company::find(auth()->user()->ins);
        $mr_no = gen4tid('REQ-', $mr->tid);
    
        if (!$company) return;
    
        $setting = RecipientSetting::where('type', 'mr_notification')
            ->where('ins', $company->id)
            ->first();
    
        if (!$setting) return;
    
        $companyName = "From " . Str::title($company->sms_email_name) . ":";
        $status = ucfirst($input['status']);
        $formattedStatus = $status === 'Amend' ? 'returned for amendment' : strtolower($status);
    
        $message = "{$companyName} - Dear {$userName}, MR {$mr_no} has been {$formattedStatus}.\n\n";
        $emailText = "Dear {$userName},\n\nWe would like to inform you that the MR, {$mr_no} has been {$formattedStatus}.";
    
        $smsData = [
            'user_type' => 'employee',
            'delivery_type' => 'now',
            'message_type' => 'single',
            'phone_numbers' => $phone_number,
            'sent_to_ids' => $user_id,
            'subject' => $message,
            'characters' => strlen($message),
            'cost' => 0.6,
            'user_count' => 1,
            'total_cost' => ceil(strlen($message) / 160) * 0.6,
        ];
    
        $emailInput = [
            'subject' => "Material Requisition Approval",
            'mail_to' => $userEmail,
            'name' => $userName,
            'text' => $emailText,
        ];
    
        // Send SMS if enabled
        if ($setting->sms === 'yes') {
            $smsResult = SendSms::create($smsData);
            (new RosesmsRepository(auth()->user()->ins))->textlocal($phone_number, $message, $smsResult);
        }
    
        // Send Email if enabled
        if ($setting->email === 'yes') {
            $emailResponse = (new RosemailerRepository(auth()->user()->ins))->send($emailText, $emailInput);
            $email_output = json_decode($emailResponse);
    
            if ($email_output->status === "Success") {
                SendEmail::create([
                    'text_email' => $emailText,
                    'subject' => $emailInput['subject'],
                    'user_emails' => $userEmail,
                    'user_ids' => $user_id,
                    'ins' => auth()->user()->ins,
                    'user_id' => auth()->user()->id,
                    'status' => 'sent',
                ]);
            }
        }
    }
    

    public function get_requisition_items(Request $request)
    {
        $purchase_request = PurchaseRequest::find($request->requisition_id);
        $items = $purchase_request->items()
        ->with(['budget_item','unit'])
        ->select('id','product_id', 'product_name', 'qty as requisition_qty','budget_item_id','unit_id','issued_qty')
        ->whereRaw('qty > issued_qty')
        ->get();
        

        $stock_transfers = StockTransfer::where('project_id', $purchase_request->project_id)
            ->where('status', 'Complete')->orWhere('status', 'Partial')
            ->with(['stock_rcvs.items' => function($query) {
                $query->select('stock_rcv_items.id','stock_rcv_items.stock_rcv_id', 'stock_rcv_items.productvar_id', 'stock_rcv_items.qty_rcv','stock_rcv_items.item_id');
            }])
            ->get();

        $mapped_items = $items->map(function($requisition_item) use ($stock_transfers) {
            $total_stock_receive_qty = 0;
            $itemId = 0;
            
            foreach ($stock_transfers as $stock_transfer) {
                foreach ($stock_transfer->stock_rcvs as $stock_receive) {
                    foreach ($stock_receive->items as $stock_receive_item) {
                        // Check if the product_id matches
                       
                        if ($stock_receive_item->productvar_id == $requisition_item->product_id) {
                            $total_stock_receive_qty += $stock_receive_item->qty_rcv;
                            $itemId = $stock_receive_item->item_id;
                        }
                    }
                }
            }

            // If no matching stock_receive_items, total_stock_receive_qty will remain 0
            return [
                'requisition_item_id' => $requisition_item->id,
                'item_id' => $itemId,
                'id' => $requisition_item->product_id,
                'name' => $requisition_item->product_name,
                'code' => $requisition_item->product ? $requisition_item->product->code : '',
                'requested_qty' => $requisition_item->requisition_qty,
                'purchase_price' => fifoCost($requisition_item->product_id) ?: $requisition_item->product->purchase_price,
                'booked_qty' => $total_stock_receive_qty, // 0 if no matching stock_receive_items found
                'budget_qty' => $requisition_item->budget_item ? $requisition_item->budget_item->new_qty : 0,
                'budget_item_id' => $requisition_item->budget_item_id,
                'issued_qty' => $requisition_item->budget_item ? $requisition_item->budget_item->issue_qty : 0,
                'unit' => @$requisition_item->unit,
                'warehouses' => Warehouse::whereHas('products', fn($q) => $q->where('name', 'LIKE', "%{$requisition_item->product_name}%"))
                ->with(['products' => fn($q) => $q->where('name', 'LIKE', "%{$requisition_item->product_name}%")])
                ->get()
                ->map(function($wh) {
                    $wh->products_qty = $wh->products->sum('qty');
                    unset($wh->product);
                    return $wh;
                }),
            ];
        });

    
        return response()->json($mapped_items);
    }
}
