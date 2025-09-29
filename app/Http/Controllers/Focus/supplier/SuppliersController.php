<?php

namespace App\Http\Controllers\Focus\supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\purchaseorder\CreatePurchaseorderRequest;
use App\Http\Requests\Focus\supplier\ManageSupplierRequest;
use App\Http\Requests\Focus\supplier\StoreSupplierRequest;
use App\Http\Responses\Focus\supplier\CreateResponse;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\billpayment\Billpayment;
use App\Models\manualjournal\Journal;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\supplier\Supplier;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Focus\supplier\SupplierRepository;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * SuppliersController
 */
class SuppliersController extends Controller
{
    /**
     * variable to store the repository object
     * @var SupplierRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param SupplierRepository $repository ;
     */
    public function __construct(SupplierRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\supplier\ManageSupplierRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageSupplierRequest $request)
    {
        return new ViewResponse('focus.suppliers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateSupplierRequestNamespace $request
     * @return \App\Http\Responses\Focus\supplier\CreateResponse
     */
    public function create(StoreSupplierRequest $request)
    {
        return new CreateResponse('focus.suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSupplierRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreSupplierRequest $request)
    {
        $request->validate([
            'ap_account_id' => 'required',
            'password' => request('password') ? 'required_with:user_email | min:7' : '',
            'password_confirmation' => 'required_with:password | same:password'
        ]);
        if (request('password')) {
            if (!preg_match("/[a-z][A-Z]|[A-Z][a-z]/i", $request->password))
                throw ValidationException::withMessages(['password' => 'Password Must Contain Upper and Lowercase letters']);
            if (!preg_match("/[0-9]/", $request->password))
                throw ValidationException::withMessages(['password' => 'Password Must Contain At Least One Number']);
            if (!preg_match("/[^A-Za-z 0-9]/", $request->password))
                throw ValidationException::withMessages(['password' => 'Password Must Contain A Symbol']);
        }

        $data = $request->only([
            'ap_account_id',
            'currency_id',
            'name',
            'phone',
            'email',
            'address',
            'city',
            'region',
            'country',
            'postbox',
            'email',
            'picture',
            'company',
            'taxid',
            'docid',
            'custom1',
            'employee_id',
            'active',
            'password',
            'role_id',
            'remember_token',
            'contact_person_info'
        ]);
        $account_data = $request->only([
            'account_name',
            'account_no',
            'open_balance',
            'open_balance_date',
            'open_balance_note',
        ]);
        $payment_data = $request->only(['bank', 'bank_code', 'payment_terms', 'credit_limit', 'mpesa_payment']);
        $user_data = $request->only('first_name', 'last_name', 'email', 'password', 'picture');
        $user_data['email'] = $request->user_email;

        try {
            $result = $this->repository->create(compact('data', 'account_data', 'payment_data', 'user_data'));
            if ($request->ajax()) {
                $result['random_password'] = null;
                return response()->json($result);
            }
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Supplier', $th);
        }

        return new RedirectResponse(route('biller.suppliers.index'), ['flash_success' => trans('alerts.backend.suppliers.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\supplier\Supplier $supplier
     * @param EditSupplierRequestNamespace $request
     * @return \App\Http\Responses\Focus\supplier\EditResponse
     */
    public function edit(Supplier $supplier, StoreSupplierRequest $request)
    {
        // load A/P accounts
        // $local_acc = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['payable', 'loan']))
        //     ->whereHas('currency', fn($q) => $q->where('rate', 1))
        //     ->first(['id', 'holder', 'currency_id']);
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['payable', 'loan']))
            ->whereHas('currency')
            ->get(['id', 'holder', 'currency_id']);
        $payroll_accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['salaries_payable', 'payroll_taxes_payable', 'health_insurance_payable', 'retirement_contribution_payable', 'other_payroll_payable']))
            ->whereHas('currency')
            ->get(['id', 'holder', 'currency_id']);    
        $accounts = collect(array_filter([]))->merge($accounts)->merge($payroll_accounts);
        // restrict currency to that of the initial bill or payment
        $bill = UtilityBill::where('supplier_id', $supplier->id)->first();
        $payment = Billpayment::where('supplier_id', $supplier->id)->first();
        if (@$bill->currency_id) $accounts = $accounts->where('currency_id', $bill->currency_id);
        elseif (@$payment->currency_id) $accounts = $accounts->where('currency_id', $payment->currency_id);

        return view('focus.suppliers.edit', compact('supplier', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSupplierRequestNamespace $request
     * @param App\Models\supplier\Supplier $supplier
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreSupplierRequest $request, Supplier $supplier)
    {
        $request->validate([
            'ap_account_id' => 'required',
            'password' => request('password') ? 'required_with:user_email | min:7' : '',
            'password_confirmation' => 'required_with:password | same:password'
        ]);
        if (request('password')) {
            if (!preg_match("/[a-z][A-Z]|[A-Z][a-z]/i", $request->password))
                throw ValidationException::withMessages(['password' => 'Password Must Contain Upper and Lowercase letters']);
            if (!preg_match("/[0-9]/", $request->password))
                throw ValidationException::withMessages(['password' => 'Password Must Contain At Least One Number']);
            if (!preg_match("/[^A-Za-z 0-9]/", $request->password))
                throw ValidationException::withMessages(['password' => 'Password Must Contain A Symbol']);
        }

        $data = $request->only([
            'ap_account_id',
            'currency_id',
            'name',
            'phone',
            'email',
            'address',
            'city',
            'region',
            'country',
            'postbox',
            'email',
            'picture',
            'company',
            'taxid',
            'docid',
            'custom1',
            'employee_id',
            'active',
            'password',
            'role_id',
            'remember_token',
            'contact_person_info'
        ]);
        $account_data = $request->only([
            'account_name',
            'account_no',
            'open_balance',
            'open_balance_date',
            'open_balance_note',
        ]);
        $payment_data = $request->only(['bank', 'bank_code', 'payment_terms', 'credit_limit', 'mpesa_payment']);
        $user_data = $request->only('first_name', 'last_name', 'password', 'picture');
        $user_data['email'] = $request->user_email;

        try {
            $result = $this->repository->update($supplier, compact('data', 'account_data', 'payment_data', 'user_data'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Supplier', $th);
        }

        return new RedirectResponse(route('biller.suppliers.index'), ['flash_success' => trans('alerts.backend.suppliers.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteSupplierRequestNamespace $request
     * @param App\Models\supplier\Supplier $supplier
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $this->repository->delete($supplier);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Supplier', $th);
        }

        return new RedirectResponse(route('biller.suppliers.index'), ['flash_success' => trans('alerts.backend.suppliers.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteSupplierRequestNamespace $request
     * @param App\Models\supplier\Supplier $supplier
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Supplier $supplier, ManageSupplierRequest $request)
    {
        $startDate = date('Y-m-d');
        $endDate = "2000-01-01";
        $supplierBills = $this->repository->agingFilteredBills($supplier->id, $startDate);
        $aging_cluster = $this->supplierAgingCluster($supplierBills, $startDate, $endDate);
        $aging_cluster = $this->agingJournalAdjustment($supplier, $aging_cluster);

        // supplier outstanding balance
        $supplier->on_account = $supplier->unallocated_amount;
        $account_balance = array_sum($aging_cluster);

        return new ViewResponse('focus.suppliers.view', compact('supplier', 'account_balance', 'aging_cluster'));
    }


    public function agingJournalAdjustment($supplier, $aging_cluster) 
    {
        $journals = Journal::doesntHave('bill')
        ->whereHas('items', fn($q) => $q->where('supplier_id', $supplier->id))
        ->with(['items' => fn($q) => $q->where('supplier_id', $supplier->id)])
        ->get(['id', 'date', 'note', 'debit_ttl', 'credit_ttl']);
        foreach ($journals as $journal) {
            $debit = +optional($journal->items->first())->debit;
            $credit = +optional($journal->items->first())->credit;
            $secondsDiff = abs(strtotime(date('Y-m-d')) - strtotime($journal->date));
            $daysDiff = floor($secondsDiff / (60*60*24));
            if ($credit > 0) {
                if ($daysDiff >= 0 && $daysDiff <= 30) {
                    $aging_cluster[0] += $credit;
                } else if ($daysDiff >= 31 && $daysDiff <= 60) {
                    $aging_cluster[1] += $credit;
                } else if ($daysDiff >= 61 && $daysDiff <= 90) {
                    $aging_cluster[2] += $credit;
                } else if ($daysDiff >= 91 && $daysDiff <= 120) {
                    $aging_cluster[3] += $credit;
                } else if ($daysDiff > 120) {
                    $aging_cluster[4] += $credit;
                }
            } else if ($debit > 0) {
                $n = count($aging_cluster)-1;
                while ($n > -1) {
                    $value = $aging_cluster[$n];
                    if ($debit <= $value) {
                        $aging_cluster[$n] -= $debit;
                        $debit = 0;
                    } else {
                        $aging_cluster[$n] -= $value;
                        $debit -= $value;
                    }
                    $n--;
                }
            }
        }

        return $aging_cluster;
    }


    /** 
     * Purchase Orders Select
     * */
    public function purchaseOrdersSelect(Request $request)
    {
        $t = $request->term;

        // purchase order grns with invoice 
        $purchaseOrders1 = Purchaseorder::where('supplier_id', $request->supplier_id)
        ->where(function($q) use($t) {
            $q->where('tid', 'LIKE', '%'. $t .'%')->orwhere('note', 'LIKE', '%'. $t .'%');
        })
        ->whereHas('grns', function($q) {
            $q->whereHas('bill');
        })
        ->get(['id', 'tid', 'note'])
        ->map(function ($v) {
            $v['tid'] = gen4tid('PO-', $v->tid);
            return $v;
        });

        // purchase order grns without invoice 
        $poIds = $purchaseOrders1->pluck('id')->toArray();
        $purchaseOrders2 = UtilityBill::where('supplier_id', $request->supplier_id)
        ->whereHas('grn_items', function($q) use($t, $poIds) {
            $q->whereHas('goodsreceivenote', function($q) use($t, $poIds) {
                $q->whereNotIn('purchaseorder_id', $poIds);
                $q->whereHas('purchaseorder', function($q) use($t) {
                    $q->where('tid', 'LIKE', '%'. $t .'%')->orwhere('note', 'LIKE', '%'. $t .'%');
                });
            });
        })
        ->with([
            'grn_items.goodsreceivenote' => function($q) use($poIds) {
                $q->whereNotIn('purchaseorder_id', $poIds);
            },
            'grn_items.goodsreceivenote.purchaseorder' => function($q) use($t) {
                $q->where('tid', 'LIKE', '%'. $t .'%')->orwhere('note', 'LIKE', '%'. $t .'%');
            },
        ])
        ->get()
        ->flatMap(function($bill) {
            return $bill->grn_items->flatMap(function($grnItem) {
                return optional($grnItem->goodsreceivenote->purchaseorder);
            });
        })
        ->filter()
        ->unique('id');

        $purchaseOrders = collect()
        ->merge($purchaseOrders1)
        ->merge($purchaseOrders2);

        return response()->json($purchaseOrders);
    }


    /**
     * Supplier Aging Summary Index Page
     */
    public function supplier_aging_report()
    {
        $suppliers = Supplier::query()
        ->where(fn($q) => $q->whereHas('bills')->orWhereHas('billPayments'))
        ->orderBy('id', 'ASC')
        // ->whereIn('id', [95])
        ->get()
        // ;dd($suppliers->toArray());
        ->map(function($supplier) {
            $startDate = date('Y-m-d');
            $endDate = "2000-01-01";
            $supplierBills = $this->repository->agingFilteredBills($supplier->id, $startDate);      
            $agingCluster = $this->supplierAgingCluster($supplierBills, $startDate, $endDate);
            // journal adjustment on aging
            $agingCluster = $this->agingJournalAdjustment($supplier, $agingCluster);

            return [
                'supplier' => $supplier,
                'aging_cluster' => $agingCluster,
                'total_aging' => round(array_sum($agingCluster), 4),
            ];
        })
        ->filter(fn($data) => (bool) round($data['total_aging']));

        // $agingArr = [];
        // foreach ($suppliers as $key => $data) {
        //     $agingArr[$data['supplier']['id']] = numberFormat($data['total_aging']);
        // }
        // dd($agingArr);
        
        return new ViewResponse('focus.suppliers.supplier_aging_report', ['suppliers_data' => $suppliers]);
    }

    /**
     * Supplier Aging Summary Filtered
     */
    public function get_supplier_aging_report(Request $request)
    {
        $request->validate(['start_date' => 'required', 'end_date' => 'required']);

        $suppliers = Supplier::query()
        ->where(fn($q) => $q->whereHas('bills')->orWhereHas('billPayments'))
        ->orderBy('id', 'ASC')
        ->get()
        ->map(function($supplier) {
            $startDate = date_for_database(request('start_date'));
            $endDate = date_for_database(request('end_date'));   
            $supplierBills = $this->repository->agingFilteredBills($supplier->id, $startDate);      
            $agingCluster = $this->supplierAgingCluster($supplierBills, $startDate, $endDate);
            // journal adjustment on aging
            $agingCluster = $this->agingJournalAdjustment($supplier, $agingCluster);

            return [
                'supplier' => '<a href="'. route('biller.suppliers.show', $supplier) .'">'.($supplier->company ?: $supplier->name).'</a>',
                'aging_cluster' => $agingCluster,
                'total_aging' => round(array_sum($agingCluster), 4),
            ];
        })
        ->filter(fn($data) => (bool) round($data['total_aging']));

        return response()->json([
            'suppliers_data' => $suppliers->values(),
        ]);
    }

    /**
     * Aging By Supplier method
     */
    public function supplierAgingCluster($bills, $startDate, $endDate)
    {
        // 5 date intervals of between 0 - 120+ days prior 
        $intervals = array();
        for ($i = 0; $i < 5; $i++) {
            $from = $startDate;
            $to = date('Y-m-d', strtotime($from . ' - 30 days'));
            if ($i > 0) {
                $prev = $intervals[$i - 1][1];
                $from = date('Y-m-d', strtotime($prev . ' - 1 day'));
                $to = date('Y-m-d', strtotime($from . ' - 28 days'));
            }
            $intervals[] = [$from, $to];
        }

        $aging_cluster = array_fill(0, 5, 0);
        foreach ($bills as $bill) {
            $due_date = new DateTime($bill->date);
            $balance = $bill->credit - $bill->debit;

            // Check if due_date is within the given date range
            if (strtotime($due_date->format('Y-m-d')) <= strtotime($startDate) && strtotime($due_date->format('Y-m-d')) >= strtotime($endDate)) {
                // Check due_date against each interval
                foreach ($intervals as $i => $dates) {
                    // dd($dates[0], $due_date);
                    $start = $dates[0];
                    $end = $dates[1];
                    if (strtotime($start) >= strtotime($due_date->format('Y-m-d')) && strtotime($end) <= strtotime($due_date->format('Y-m-d'))) {
                        $aging_cluster[$i] += $balance;
                        break;
                    }
                }
                // If due_date is older than the last interval, categorize it as 120+ days
                if (strtotime($due_date->format('Y-m-d')) < strtotime($intervals[4][1])) {
                    $aging_cluster[4] += $balance;
                }
            }
        }
        return $aging_cluster;
    }

    public function aging($supplier)
    {
        // 5 date intervals of between 0 - 120+ days prior 
        $intervals = array();
        for ($i = 0; $i < 5; $i++) {
            $from = date('Y-m-d');
            $to = date('Y-m-d', strtotime($from . ' - 30 days'));
            if ($i > 0) {
                $prev = $intervals[$i - 1][1];
                $from = date('Y-m-d', strtotime($prev . ' - 1 day'));
                $to = date('Y-m-d', strtotime($from . ' - 28 days'));
            }
            $intervals[] = [$from, $to];
        }

        // statement on bills 
        $bills = collect();
        $bills_statement = $this->repository->getStatementForDataTable($supplier->id);
        foreach ($bills_statement as $row) {
            if ($row->type == 'bill') $bills->add($row);
            else {
                $last_bill = $bills->last();
                if ($last_bill->bill_id == $row->bill_id) {
                    $last_bill->debit += $row->debit;
                }
            }
        }

        // aging balance from extracted invoices
        $aging_cluster = array_fill(0, 5, 0);
        foreach ($bills as $bill) {
            $due_date = new DateTime($bill->date);
            $debt_amount = $bill->credit - $bill->debit;
            // over payment
            if ($debt_amount < 0) {
                // $supplier->on_account += $debt_amount * -1;
                $debt_amount = 0;
            }
            // due_date between 0 - 120 days
            foreach ($intervals as $i => $dates) {
                $start  = new DateTime($dates[0]);
                $end = new DateTime($dates[1]);
                if ($start >= $due_date && $end <= $due_date) {
                    $aging_cluster[$i] += $debt_amount;
                    break;
                }
            }
            // due_date in 120+ days
            if ($due_date < new DateTime($intervals[4][1])) {
                $aging_cluster[4] += $debt_amount;
            }
        }

        // supplier debt balance
        $account_balance = collect($aging_cluster)->sum() - $supplier->on_account;
        return $aging_cluster;
    }

    public function check_limit(Request $request)
    {
        $supplier = Supplier::find($request->supplier_id);
        $aging_cluster = $this->aging($supplier);
        $total_aging = 0;
        for ($i = 0; $i < count($aging_cluster); $i++) {
            $total_aging += $aging_cluster[$i];
        }

        return response()->json([
            'total_aging' => floatval($total_aging),
            'outstanding_balance' => floatval($supplier->on_account),
            'credit_limit' => floatval($supplier->credit_limit),
        ]);
    }

    public function search(CreatePurchaseorderRequest $request)
    {
        $q = $request->post('keyword');
        $user = Supplier::where('name', 'LIKE', '%' . $q . '%')
            ->where('active', 1)
            ->orWhere('email', 'LIKE', '%' . $q . '')
            ->limit(6)->get(['id', 'name', 'phone', 'address', 'city', 'email']);

        return view('focus.suppliers.partials.search')->with(compact('user'));
    }

    /**
     * Supllier select dropdown
     */
    public function select(Request $request)
    {
        $k = $request->keyword;
        $suppliers = Supplier::where('name', 'LIKE', '%' . $k . '%')
            ->orWhere('company', 'LIKE', '%' . $k . '%')
            ->with(['currency' => fn($q) => $q->select('id', 'code', 'rate')->get()])
            ->limit(6)
            ->get(['id', 'name', 'phone', 'address', 'city', 'email', 'taxid', 'currency_id']);

        return response()->json($suppliers);
    }

    public function active(ManageSupplierRequest $request)
    {

        $cid = $request->post('cid');
        $active = $request->post('active');
        $active = !(bool)$active;
        Supplier::where('id', '=', $cid)->update(array('active' => $active));
    }

    /**
     * Get Purchase Orders
     */
    public function purchaseorders()
    {
        if (request('type') == 'grn') {
            $purchase_orders = Purchaseorder::where('supplier_id', request('supplier_id'))
                ->where('currency_id', request('currency_id'))
                ->whereIn('status', ['Pending', 'Partial'])
                ->where('closure_status', 0)
                ->get();
        } else {
            $purchase_orders =  Purchaseorder::where('supplier_id', request('supplier_id'))->get();
        }

        return response()->json($purchase_orders);
    }

    /**
     * Get Goods receive note
     */
    public function goods_receive_note()
    {
        $supplier = Supplier::find(request('supplier_id'));
        $grns = $supplier ? $supplier->goods_receive_notes : [];

        return response()->json($grns);
    }

    /**
     * Get Supplier Bills
     */
    public function bills()
    {
        $bills = UtilityBill::where('supplier_id', request('supplier_id'))
            ->whereColumn('amount_paid', '<', 'total')
            ->with([
                'supplier' => fn($q) => $q->select('id', 'name'),
                'purchase' => fn($q) => $q->select('id', 'suppliername', 'note'),
                'grn' => fn($q) => $q->select('id', 'note'),
            ])
            ->orderBy('due_date', 'asc')->get()
            ->map(function ($v) {
                if ($v->document_type == 'direct_purchase') {
                    $v->suppliername = $v->purchase ? $v->purchase->suppliername : '';
                    if ($v->grn) unset($v->grn);
                } elseif ($v->document_type == 'goods_receive_note') {
                    if ($v->purchase) unset($v->purchase);
                }

                return $v;
            });
        if (request('supplier_name') || request('bill_number') || request('start_date')) {
            $bills = UtilityBill::where('supplier_id', request('supplier_id'))
                ->whereColumn('amount_paid', '<', 'total')
                ->when(request('bill_number'), fn($q) => $q->where('tid', request('bill_number')))
                ->when(request('start_date') && request('end_date'), function ($q) {
                    $q->whereBetween('due_date', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
                })
                ->when(request('supplier_name'), fn($q) => $q->whereHas('purchase', function ($q) {
                    $q->where('suppliername', request('supplier_name'));
                }))
                ->with([
                    'supplier' => fn($q) => $q->select('id', 'name'),
                    'purchase' => fn($q) => $q->select('id', 'suppliername', 'note'),
                    'grn' => fn($q) => $q->select('id', 'note'),
                ])
                ->orderBy('due_date', 'asc')->get()
                ->map(function ($v) {
                    if ($v->document_type == 'direct_purchase') {
                        $v->suppliername = $v->purchase ? $v->purchase->suppliername : '';
                        if ($v->grn) unset($v->grn);
                    } elseif ($v->document_type == 'goods_receive_note') {
                        if ($v->purchase) unset($v->purchase);
                    }

                    return $v;
                });
        }
        $supplierNames = $bills->map(function ($bill) {
            $supplier_name = '';
            if ($bill->document_type == 'direct_purchase') {
                $supplier_name =  $bill->purchase->suppliername ?? null;
                return $supplier_name;
            } elseif ($bill->document_type == 'goods_receive_note') {
                $supplier_name = $bill->suppliername ?? null;
                return $supplier_name;
            }
            return $supplier_name;
        })->filter()->unique()->values();
        $bill_numbers = $bills->map(function ($bill) {
            return $bill->tid;
        })->filter()->unique()->values();


        $response = ['bills' => $bills, 'bill_numbers' => $bill_numbers, 'supplier_names' => $supplierNames];
        return response()->json($response);
    }
}
