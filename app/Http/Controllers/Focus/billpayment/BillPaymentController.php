<?php

namespace App\Http\Controllers\Focus\billpayment;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\billpayment\Billpayment;
use App\Models\casual\CasualLabourer;
use App\Models\hrm\Hrm;
use App\Models\supplier\Supplier;
use App\Models\third_party_user\ThirdPartyUser;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Focus\billpayment\BillPaymentRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillPaymentController extends Controller
{
    /**
     * Store repository object
     * @var \App\Repositories\Focus\billpayment\BillPaymentRepository
     */
    public $repository;

    public function __construct(BillPaymentRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $suppliers = Supplier::get(['id', 'name']);
        return view('focus.billpayments.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $tid = Billpayment::where('ins', auth()->user()->ins)->max('tid');
        $accounts = Account::where(function ($q) {
                $q->whereNull('system')
                ->orWhere('system', 'pool_petty_cash');
            })
            ->whereHas('accountType', fn($q) =>  $q->where('system', 'bank'))
            ->get(['id', 'holder', 'currency_id','system']);

        $suppliers = Supplier::orderByRaw("
                CASE 
                    WHEN LOWER(name) = LOWER(?) THEN 1 
                    ELSE 2 
                END", ['Walk-In'])
            ->orderByRaw('LOWER(name) ASC')
            ->get(['id', 'name', 'currency_id']);

        $employees = User::get();

        $direct_bill = [];
        $params = $request->only(['src_id', 'src_type']);
        if (count($params) == 2) {
            $bill = UtilityBill::where([
                'ref_id' => $params['src_id'],
                'document_type' => $params['src_type'],
                'status' => 'due'
            ])->first();
            if ($params['src_type'] == 'direct_purchase' && !$bill) {
                return redirect(route('biller.purchases.index'))->with([
                    'flash_error' => 'Bill Unavailable For Direct Payment.'
                ]);
            } else {
                $direct_bill = [
                    'tid' => $bill->tid,
                    'supplier_id' => $bill->supplier_id,
                    'amount' => $bill->total,
                ];
            }
        }

        $unallocated_pmts = Billpayment::whereIn('payment_type', ['on_account', 'advance_payment'])
            ->whereColumn('amount', '!=', 'allocate_ttl')
            ->orderBy('date', 'asc')->get();
        $users = Hrm::all();
        $third_party_users = ThirdPartyUser::all();
        $casuals = CasualLabourer::all();

        return view('focus.billpayments.create', compact('tid', 'accounts', 'suppliers', 'employees', 'direct_bill', 'unallocated_pmts','users','third_party_users','casuals'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'amount' => [
                function (string $attribute, $value, Closure $fail) {

                    if (floatval($value) <= 0) $fail('A non-zero amount is required');
                }
            ],
            'reference' => [

                function (string $attribute, $value, Closure $fail) use ($request) {

                    if (!isset($request['rel_payment_id']) && $request['reference'] && $request['account_id']) {
                        $is_duplicate_ref = Billpayment::where('account_id', $request['account_id'])
                            ->where('reference', 'LIKE', "%{$request['reference']}%")
                            ->whereNull('rel_payment_id')
                            ->exists();
                        if ($is_duplicate_ref) {
                            $fail('Duplicate reference no.');
                        }
                    }
                },
            ],
        ]);



        try {
            DB::beginTransaction();

            $this->repository->create($request->except('_token', 'balance', 'unallocate_ttl'));

            DB::commit();

        } catch (\Exception $e){
            DB::rollBack();
            return "Error: '" . $e->getMessage() . " | on File: " . $e->getFile() . "  | & Line: " . $e->getLine();
        }

        return new RedirectResponse(route('biller.billpayments.index'), ['flash_success' => 'Bill Payment Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\billpayment\Billpayment $billpayment
     * @return \Illuminate\Http\Response
     */
    public function edit(Billpayment $billpayment)
    {
        $suppliers = Supplier::orderByRaw("
                CASE 
                    WHEN LOWER(name) = LOWER(?) THEN 1 
                    ELSE 2 
                END", ['Walk-In'])
            ->orderByRaw('LOWER(name) ASC')
            ->get(['id', 'name', 'currency_id']);

        $employees = User::get();
        $accounts = Account::where(function ($q) {
                $q->whereNull('system')
                ->orWhere('system', 'pool_petty_cash');
            })
            ->whereHas('accountType', fn($q) =>  $q->where('system', 'bank'))
            ->get(['id', 'holder', 'currency_id','system']);

        $unallocated_pmts = Billpayment::where('payment_type', '!=', 'per_invoice')
            ->whereColumn('amount', '!=', 'allocate_ttl')
            ->orderBy('date', 'asc')->get();

        $is_allocated_pmt = false;
        $has_allocations = Billpayment::where('rel_payment_id', $billpayment->id)->exists();
        if ($billpayment->payment_type != 'per_invoice' && $has_allocations) $is_allocated_pmt = true;
            
        $is_next_allocation = false;
        $allocation_count = Billpayment::where('rel_payment_id', $billpayment->id)->count();
        if ($allocation_count > 1) $is_next_allocation = true;

        $users = Hrm::all();
        $third_party_users = ThirdPartyUser::all();
        $casuals = CasualLabourer::all();
        
        return view('focus.billpayments.edit', 
            compact('billpayment', 'accounts', 'suppliers', 'employees', 'unallocated_pmts', 'is_allocated_pmt', 'is_next_allocation','users','third_party_users','casuals')
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\billpayment\Billpayment $billpayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Billpayment $billpayment)
    {
        // check if reconciled
        if ($billpayment->reconciliation_items()->where('checked',1)->exists()) {
            $reqAmount = numberClean($request->amount); 
            $billAmount = numberClean($billpayment->amount); 
            if (floatval($reqAmount) != floatval($billAmount)) {
                return errorHandler('Not Allowed! Payment has been reconciled');
            }
        }

        try {
            $this->repository->update($billpayment, $request->except('_token', 'balance', 'unallocate_ttl'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Bill Payment!', $th);
        }

        return new RedirectResponse(route('biller.billpayments.index'), ['flash_success' => 'Bill Payment Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\billpayment\Billpayment $billpayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Billpayment $billpayment)
    {
        if ($billpayment->reconciliation_items()->where('checked', 1)->exists()) 
            return errorHandler('Not Allowed! Payment has been reconciled');
        
        try {
            $this->repository->delete($billpayment);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Bill Payment!', $th);
        }

        return new RedirectResponse(route('biller.billpayments.index'), ['flash_success' => 'Bill Payment Deleted Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\billpayment\Billpayment $billpayment
     * @return \Illuminate\Http\Response
     */
    public function show(Billpayment $billpayment)
    {
        return view('focus.billpayments.view', compact('billpayment'));
    }
}
