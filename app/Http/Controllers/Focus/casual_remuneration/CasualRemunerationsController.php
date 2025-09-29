<?php

namespace App\Http\Controllers\Focus\casual_remuneration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\casualLabourersRemuneration\StoreCasualLabourersRemunerations;
use App\Http\Requests\Focus\casualLabourersRemuneration\UpdateCasualLabourersRemunerations;
use App\Models\account\Account;
use App\Models\casual_labourer_remuneration\CasualLabourersRemuneration;
use App\Models\currency\Currency;
use App\Models\labour_allocation\LabourAllocation;
use App\Models\supplier\Supplier;
use App\Models\utility_bill\UtilityBill;
use App\Models\wage_item\WageItem;
use App\Repositories\Accounting;
use App\Repositories\Focus\casual_remuneration\CasualRemunerationRepository;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CasualRemunerationsController extends Controller
{
    use Accounting;

    const PERMISSION_ERROR_MSG = 'Insufficient Permissions';

    /**
     * variable to store the repository object
     * @var CasualRemunerationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param CasualRemunerationRepository $repository ;
     */
    public function __construct(CasualRemunerationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!access()->allow('manage-casual-labourers-remuneration')) {
            return redirect()->back()->with('flash_error', self::PERMISSION_ERROR_MSG);
        }

        // $tenants = CasualLabourersRemuneration::withoutGlobalScopes()->select('ins')->distinct()->pluck('ins');
        // foreach ($tenants as $id) {
        //     $remuns =  CasualLabourersRemuneration::withoutGlobalScopes()
        //     ->where('ins', $id)
        //     ->orderBy('created_at', 'ASC')
        //     ->get();
        //     foreach ($remuns as $key => $remun) {
        //         $remun->update(['tid' => $key+1]);
        //     }
        // }

        return view('focus.casual_remunerations.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!access()->allow('create-casual-labourers-remuneration')) {
            return redirect()->back()->with('flash_error', self::PERMISSION_ERROR_MSG);
        }

        $tid = CasualLabourersRemuneration::max('tid')+1;

        // unpaid labour allocations
        $labourAllocations = LabourAllocation::query()
            ->doesntHave('casualLabourersRemuneration')
            ->whereHas('casualLabourers')
            ->with('casualLabourers')
            ->orderBy('date')
            ->get();

        $wageItems = WageItem::whereHas('casualLabourers', function($q) use($labourAllocations) {
            $q->whereHas('labourAllocations', function($q) use($labourAllocations) {
                $q->whereIn('labour_allocations.id', $labourAllocations->pluck('id')->toArray());
            });
        })
        ->whereNotIn('earning_type', ['overtime', 'regular_pay'])
        ->get(['id', 'name', 'earning_type']);
        
        return view('focus.casual_remunerations.create', compact('tid', 'labourAllocations', 'wageItems'));
    }

    /** 
     * Labour Allocation Jobcard details
     * */
    public function getJobCardDetails()
    {
        $allocations = LabourAllocation::whereIn('id', request('laId', []))
            ->with(['project', 'project.quote', 'project.quote.lead', 'project.customer'])
            ->get()
            ->map(function ($v) {
                $quote = optional($v->project)->quote;
                $quoteRef = $quote? gen4tid($quote->bank_id? 'PI-': 'QT-', $quote->tid) . ' | ' . Str::limit($quote->notes, 80) : '';
                $lead =  optional(optional($v->project)->quote)->lead;
                $leadRef = $lead? gen4tid('TKT-', $lead->reference). ' | ' . $lead->title : '';
                $customer =  optional(optional($v->project)->customer);
                $customerRef = $customer? gen4tid('CRM-', $customer->id). ' | ' . $customer->name : '';
                return [
                    'id' => $v->id,
                    'date' => $v->date,
                    'link' => "<a href=". route('biller.labour_allocations.edit', $v) .">". ($v->job_card ?: 'link') ."</a>",
                    'job_card' => $v->job_card,
                    'project' => @$v->project->name,
                    'quote' => $quote ? $quoteRef : '',
                    'lead' => $lead ? $leadRef : '',
                    'customer' => $customer ? $customerRef : '',
                ];
            });

        return response()->json($allocations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCasualLabourersRemunerations $request)
    {
        $request->validate([
            'title' => [
                function (string $attribute, $value, Closure $fail) {
                    $existingTitle = CasualLabourersRemuneration::where('title', $value)->first();
                    if (!empty($existingTitle)) {
                        $fail("Action Denied! You already have a Casual Labourers' Remuneration by this Title");
                    }
                },
            ],
        ]);
        $input = $request->validated();

        try{
            $this->repository->create($input);
        } catch (Exception $e){
            return errorHandler('Error Creating Remuneration', $e);
        }
        
        return redirect()->route('biller.casual_remunerations.index')->with('success', 'Remuneration Saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $clrNumber)
    {
        if (!access()->allow('manage-casual-labourers-remuneration')) {
            return redirect()->back()->with('flash_error', self::PERMISSION_ERROR_MSG);
        }

        $clR = CasualLabourersRemuneration::findOrFail($clrNumber);
        $labourAllocationIdsArray = $clR->labourAllocations->map(function ($lA){
            return $lA->pivot->labour_allocation_id;
        })->toArray();
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'cog_labour'))
            ->get();

        $labourAllocations = LabourAllocation::query()
            ->whereHas('casualLabourers')
            ->doesntHave('casualLabourersRemuneration')
            ->with('casualLabourers')
            ->orderBy('date')
            ->get();

        $labourAllocations = LabourAllocation::whereDoesntHave('clrPivot')
            ->whereHas('casualLabourers')
            ->orWhereIn('id', $labourAllocationIdsArray)
            ->orderBy('date')
            ->get();
        $casualsIds = [];
        foreach ($labourAllocations as $key => $allocation) {
            $casualsIds = array_merge($casualsIds, $allocation->casualLabourers->pluck('id')->toArray());
        }
        $wageItems = WageItem::whereHas('casualLabourers', fn($q) => $q->whereIn('casual_labourers.id', array_unique($casualsIds)))
        ->whereNotIn('earning_type', ['overtime', 'regular_pay'])
        ->with(['clrWageItems' => fn($q) => $q->where('clr_number', $clrNumber)])
        ->get(['id', 'name', 'earning_type']);  

        return view('focus.casual_remunerations.show', compact('clR', 'wageItems', 'labourAllocationIdsArray', 'accounts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $clrNumber)
    {
        if (!access()->allow('edit-casual-labourers-remuneration')) {
            return redirect()->back()->with('flash_error', self::PERMISSION_ERROR_MSG);
        }

        $casualLabourersRemuneration = CasualLabourersRemuneration::findOrFail($clrNumber);

        $tid = $casualLabourersRemuneration->tid;

        $labourAllocationIdsArray = $casualLabourersRemuneration->labourAllocations
            ->map(function ($lA){
                return $lA->pivot->labour_allocation_id;
            })
            ->toArray();

        $labourAllocations = LabourAllocation::whereDoesntHave('clrPivot')
            ->whereHas('casualLabourers')
            ->orWhereIn('id', $labourAllocationIdsArray)
            ->orderBy('date')
            ->get();
        $casualsIds = [];
        foreach ($labourAllocations as $key => $allocation) {
            $casualsIds = array_merge($casualsIds, $allocation->casualLabourers->pluck('id')->toArray());
        }
        $wageItems = WageItem::whereHas('casualLabourers', fn($q) => $q->whereIn('casual_labourers.id', array_unique($casualsIds)))
        ->whereNotIn('earning_type', ['overtime', 'regular_pay'])
        ->with(['clrWageItems' => fn($q) => $q->where('clr_number', $clrNumber)])
        ->get(['id', 'name', 'earning_type']);          

        return view('focus.casual_remunerations.edit', compact('tid', 'wageItems', 'casualLabourersRemuneration', 'labourAllocations', 'labourAllocationIdsArray'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCasualLabourersRemunerations $request, string $clrNumber)
    {
        $request->validate([
            'title' => [
                function (string $attribute, $value, Closure $fail) use ($clrNumber){
                    $existingTitle = CasualLabourersRemuneration::where('clr_number', '!=', $clrNumber)->where('title', $value)->first();
                    if (!empty($existingTitle)) {
                        $fail("Action Denied! You already have a Casual Labourers' Remuneration by this Title");
                    }
                },
            ],
        ]);
        $input = $request->validated();
        $input['labour_allocation_id'] = $request->labour_allocation_id;

        try{
            $remuneration = CasualLabourersRemuneration::find($clrNumber);
            $this->repository->update($remuneration, $input);
        } catch (Exception $e){
            return errorHandler('Error Updating Remuneration', $e);
        }

        return redirect()->route('biller.casual_remunerations.index')->with('success', 'Remuneration Saved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $clrNumber)
    {
        if (!access()->allow('delete-casual-labourers-remuneration')) {
            return redirect()->back()->with('flash_error', self::PERMISSION_ERROR_MSG);
        }

        try {
            $casualRemun = CasualLabourersRemuneration::find($clrNumber);
            $this->repository->delete($casualRemun);
        } catch (\Exception $e) {
            return errorHandler('Error Deleting Remuneration', $e);
        }

        return redirect(route('biller.casual_remunerations.index'))->with('flash_success', 'Remuneration Deleted Successfully');
    }

    /** 
     * Approve Casuals Wages
     * */
    public function updateApproval($clrNumber) 
    {
        if (!access()->allow('approve-casual-labourers-remuneration')) {
            return redirect()->back()->with('flash_error', 'Insufficient Permission to approve');
        }
        $input = request()->only('status', 'approval_note', 'exp_account_id');

        try {
            DB::beginTransaction();

            // approve remuneration
            $casualRemun = CasualLabourersRemuneration::find($clrNumber);
            $casualRemun->update(array_replace($input, [
                'approved_by' => auth()->user()->id,
            ]));

            /** accounting **/
            if ($casualRemun->status == 'APPROVED') {
                $bill = $this->createBill($casualRemun);
                $bill->project_id = $casualRemun->labourAllocations()->first()->project_id;
                $this->post_casual_wage_bill($bill);
            } 

            DB::commit();
        } catch (\Exception $e) {
            return errorHandler('Error Approving Remuneration', $e);
        }

        return redirect(route('biller.casual_remunerations.show', $clrNumber))
            ->with('flash_success', 'Remuneration Approved successfully.');
    }

    /** 
     * Generate Bill for the approved wage
     * */
    public function createBill($casualRemun)
    {
        $currency = Currency::where('rate', 1)->first();
        $supplier = Supplier::whereHas('ap_account', function($q) {
            $q->whereHas('account_type_detail', fn($q) => $q->where('system', 'salaries_payable'));
        })->first();
        if (!$supplier) throw ValidationException::withMessages(['Salaries Payable supplier type required']);

        $casualRemunAmount = $casualRemun->total_amount;
        $bill = $casualRemun->bill;
        if ($bill) {
            $bill->transactions()->delete();
            $bill->update([
                'supplier_id' => $supplier->id,
                'currency_id' => $currency->id,
                'date' => $casualRemun->date, 
                'note' => $casualRemun->title,
                'due_date' => $casualRemun->date,
                'subtotal' => $casualRemunAmount,
                'total' => $casualRemunAmount,
            ]);
            $bill->items()->first()->update([
                'note' => $casualRemun->title,
                'qty' => 1,
                'subtotal' => $casualRemunAmount,
                'total' => $casualRemunAmount,
            ]);
        } else {
            $bill = UtilityBill::create([
                'tid' => UtilityBill::max('tid')+1,
                'supplier_id' => $supplier->id,
                'currency_id' => $currency->id,
                'document_type' => 'casual_wage',
                'casual_remun_id' => $casualRemun->clr_number,
                'date' => $casualRemun->date, 
                'due_date' => $casualRemun->date,
                'note' => $casualRemun->title,
                'subtotal' => $casualRemunAmount,
                'total' => $casualRemunAmount,
            ]);
            $bill->items()->create([
                'bill_id' => $bill->id,
                'note' => $casualRemun->title,
                'qty' => 1,
                'subtotal' => $casualRemunAmount,
                'total' => $casualRemunAmount,
            ]);                
        }
        return $bill;
    }
}
