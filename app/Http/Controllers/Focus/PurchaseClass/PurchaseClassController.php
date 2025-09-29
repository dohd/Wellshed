<?php

namespace App\Http\Controllers\Focus\PurchaseClass;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\account\Account;
use App\Models\items\PurchaseItem;
use App\Models\product\ProductVariation;
use App\Models\purchase\Purchase;
use App\Models\purchaseClass\ExpenseCategory;
use App\Models\purchaseClass\PurchaseClass;
use App\Models\PurchaseClassBudgets\PurchaseClassBudget;
use App\Models\supplier_product\SupplierProduct;
use Cassandra\Date;
use Closure;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PurchaseClassController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (!access()->allow('manage-purchase-class')) return redirect()->back();

        $expenseCategories = ExpenseCategory::whereHas('purchaseClass')->get();


        if ($request->ajax()) {

            $purchaseClasses = PurchaseClass::when(request('expenseCategoryFilter'), function ($q) {
                    $q->where('expense_category', intval(request('expenseCategoryFilter')));
                })->get();

            return Datatables::of($purchaseClasses)

                ->editColumn('expense_category', function ($model){

                    return $model->expenseCategory ? $model->expenseCategory->name : '<b style="color: #FF8200"><i> Expense Category Not Set </i></b>';
                })

                ->addColumn('action', function ($model) {

                    $route = route('biller.purchase-classes.edit', $model->id);
                    $routeShow = route('biller.purchase-classes.show', $model->id);
                    $routeDelete = route('biller.purchase-classes.destroy', $model->id);

                    return '<a href="'.$route.'" class="btn btn-secondary round mr-1">Edit</a>'
                        . '<a href="' .$routeDelete . '" 
                            class="btn btn-danger round" data-method="delete"
                            data-trans-button-cancel="' . trans('buttons.general.cancel') . '"
                            data-trans-button-confirm="' . trans('buttons.general.crud.delete') . '"
                            data-trans-title="' . trans('strings.backend.general.are_you_sure') . '" 
                            data-toggle="tooltip" 
                            data-placement="top" 
                            title="Delete"
                            >
                                <i  class="fa fa-trash"></i>
                            </a>';

                })
                ->rawColumns(['expense_category','action'])
                ->make(true);

        }


        return view('focus.purchase_classes.index', compact('expenseCategories'));
    }

    public function reportIndex(Request $request)
    {

        if ($request->ajax()) {

            $purchaseClasses = PurchaseClass::all();

            return Datatables::of($purchaseClasses)
                ->addColumn('action', function ($model) {

                    $routeShow = route('biller.purchase-classes.show', $model->id);

                    return '<a href="'.$routeShow.'" class="btn btn-secondary round mr-1">Reports</a>';
                })
                ->rawColumns(['action'])
                ->make(true);

        }


        return view('focus.purchase_classes.reports');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!access()->allow('create-purchase-class')) return redirect()->back();

        $expenseCategories = ExpenseCategory::all();

        return view('focus.purchase_classes.create', compact('expenseCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {

        if (!access()->allow('create-purchase-class')) return redirect()->back();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255',
                    function (string $attribute, $value, Closure $fail) {

                        $existingName = PurchaseClass::where('name', $value)->first();

                        if ($existingName) {
                            $fail("You already have a Non-Project Class by the name '" . $value . "'.");
                        }
                    },
            ],
            'expense_category' => ['required', 'exists:expense_categories,id']
        ]);

        $purchaseClass = new PurchaseClass();
        $purchaseClass->fill($validated);
        $purchaseClass->ins = auth()->user()->ins;

        $purchaseClass->save();

        return new RedirectResponse(route('biller.purchase-classes.index'), ['flash_success' => "Non-Project Class '" . $purchaseClass->name . "' saved successfully"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!access()->allow('manage-purchase-class')) return redirect()->back();

        $purchaseClass = PurchaseClass::find($id);

        $purchases = PurchaseClass::where('id', $id)
            ->with('purchases.project', 'purchases.budgetLine', 'purchases.supplier', 'purchases.creator')
            ->first();
        $purchaseOrders = PurchaseClass::where('id', $id)
            ->with('purchaseOrders.project', 'purchaseOrders.budgetLine', 'purchaseOrders.supplier', 'purchaseOrders.creator')
            ->first();


        return view('focus.purchase_classes.show', compact('purchaseClass', 'purchases', 'purchaseOrders'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!access()->allow('edit-purchase-class')) return redirect()->back();

        $purchaseClass = PurchaseClass::find($id);
        $expenseCategories = ExpenseCategory::all();

        return view('focus.purchase_classes.edit', compact('purchaseClass', 'expenseCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return RedirectResponse
     * @throws \Exception
     */
    public function update(Request $request, $id)
    {

        if (!access()->allow('edit-purchase-class')) return redirect()->back();


        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255',
                    function (string $attribute, $value, Closure $fail) use($id) {

                        $existingName = PurchaseClass::where('id', '!=', $id)
                            ->where('name', $value)->first();

                        if (!empty($existingName)) {
                            $fail("You already have a Non-Project Class by the name '" . $value . "'.");
                        }
                    },
            ],
            'expense_category' => ['required', 'exists:expense_categories,id']
        ]);

        $purchaseClass = PurchaseClass::find($id);
        $purchaseClass->fill($validated);
        $purchaseClass->save();

        return new RedirectResponse(route('biller.purchase-classes.index'), ['flash_success' => "Non-Project Class '" . $purchaseClass->name . "' updated successfully"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        if (!access()->allow('delete-purchase-class')) return redirect()->back();

        $purchaseClass = PurchaseClass::find($id);

        $name = $purchaseClass->name;

        if (count($purchaseClass->budgets) > 0){
            return redirect()->route('biller.purchase-classes.index')
                ->with('flash_error', 'Delete Blocked as Non-Project Class is Allocated to ' . count($purchaseClass->budgets) . ' budget(s)');
        }

        $purchaseClass->delete();

        return new RedirectResponse(route('biller.purchase-classes.index'), ['flash_success' => "Non-Project Class '" . $name . "' deleted successfully"]);
    }

    public function purchaseClassBreviary(Request $request)
    {

        try {

            $purchaseClasses = PurchaseClass::orderBy('name')->whereHas('budgets.purchaseItems')->get();
            $expenseCategories = ExpenseCategory::orderBy('name')->whereHas('purchaseClass.budgets.purchaseItems')->get();

            if ($request->ajax()) {

                $purchaseRecords = $this->breviaryData($request)['purchaseRecords'];

                return Datatables::of($purchaseRecords)
                    ->editColumn('total', function ($model){

                        return number_format($model['total'], 2);
                    })
                    ->rawColumns(['items', 'p_number'])
                    ->make(true);

            }

        }
        catch(Exception $ex){

//            if (Auth::user()->roles()->first()->id === 17)
            return [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ];

//            else
//                return ['An error occurred. Please await support'];
        }

        return view('focus.purchase_classes.breviary', compact('purchaseClasses', 'expenseCategories'));
    }


    public function breviaryData(Request $request) {

        $purchaseRecords = [];

        try {

            $monthBudget = '0.00';

            $months = [1 => 'january', 2 => 'february', 3 => 'march', 4 => 'april', 5 => 'may', 6 => 'june', 7 => 'july', 8 => 'august', 9 => 'september', 10 => 'october', 11 => 'november', 12 => 'december'];
            if (request('monthFilter')) {

                $pcbMonthBudget = PurchaseClassBudget::
                    when(request('purchaseClassFilter'), function ($r) {

                        $r->where('purchase_class_id', request('purchaseClassFilter'));
                    })
                    ->when(request('expenseCategoryFilter'), function ($s) {

                        $s->whereHas('purchaseClass', function ($t) {

                            $t->where('expense_category', request('expenseCategoryFilter'));
                        });

                    })
                    ->pluck($months[request('monthFilter')])
                    ->sum();


                $monthBudget = $pcbMonthBudget ?? '0.00';
            }
            else $monthBudget = '0.00';


            $purchaseRecords = Purchase::whereHas('products.purchaseClassBudget', function ($q) {
                    $q->when(request('purchaseClassFilter'), function ($r) {
                        $r->where('purchase_class_id', request('purchaseClassFilter'));
                    });
                })
                ->when(request('expenseCategoryFilter'), function ($q) {

                    $q->whereHas('products.purchaseClassBudget.purchaseClass', function ($r) {
                        $r->where('expense_category', request('expenseCategoryFilter'));
                    });
                })
                ->when(request('monthFilter'), function ($q) {
                    $q->whereMonth('date', intval(request('monthFilter')));
                })
                ->when(request('yearFilter'), function ($q) {
                    $q->whereYear('date', intval(request('yearFilter')));
                })
                ->when(request('fromDateFilter'), function ($q) {
                    $q->whereDate('date',  '>=', (new DateTime(request('fromDateFilter')))->format('Y-m-d'));
                })
                ->when(request('toDateFilter'), function ($q) {
                    $q->whereDate('date',  '<=', (new DateTime(request('toDateFilter')))->format('Y-m-d'));
                })
                ->get()
                ->map(function ($purchase) {

                    $items = PurchaseItem::where('bill_id', $purchase->id)
                        ->whereNotNull('purchase_class_budget')
                        ->select('item_id', 'type', 'amount', 'purchase_class_budget')
                        ->get()
                        ->map(function ($pItem) {

//                        $itemList = '';

                            $pcb = PurchaseClassBudget::find($pItem->purchase_class_budget);
                            $pClass = PurchaseClass::find(optional($pcb)->purchase_class_id);
                            $expCat = ExpenseCategory::find(optional($pClass)->expense_category);

                            if ($pItem->type === 'Stock') {

                                $name = '';

                                $productVariation = ProductVariation::find($pItem->item_id);
                                $supplierProduct = SupplierProduct::find($pItem->item_id);
                                if ($productVariation) $name = '<span> <b>Stock |</b>' . $productVariation->name . '<b> | Value: </b>' . number_format($pItem->amount, 2) . '</span><br>';
                                else $name = '<span> <b>Class:</b> <i>' . optional($pClass)->name . '</i> || <b>Category:</b> <i>' . optional($expCat)->name . '</i> || <b>Stock |</b>' . $supplierProduct->decr . '<b> | Value: </b>' . number_format($pItem->amount, 2) . '</span><br>';

//                            $itemList .= $name;
                            } else if ($pItem->type === 'Expense') {

                                $account = Account::find($pItem->item_id);

                                $name = '<span> <b>Class:</b> <i>' . optional($pClass)->name . '</i> || <b>Category:</b> <i>' . optional($expCat)->name . '</i> || <b>Expense |</b>' . optional($account)->holder . '<b> | Value: </b>' . number_format($pItem->amount, 2) . '</span><br>';
                            }

                            return $name;
                        });


                    $totalItems = PurchaseItem::where('bill_id', $purchase->id)
                        ->whereNotNull('purchase_class_budget')
                        ->pluck('amount')
                        ->toArray();

                    $itemIds = PurchaseItem::where('bill_id', $purchase->id)
                        ->whereNotNull('purchase_class_budget')
                        ->select('id')
                        ->get()
                        ->pluck('id');

                    return [
                        'p_number' => '<a href="' . route('biller.purchases.show', $purchase->id) . '"><b>' . 'DP-' . str_pad($purchase->tid, 4, '0', STR_PAD_LEFT) . '</b></a>',
                        'note' => $purchase->note,
                        'supplier' => optional($purchase->supplier)->name,
                        'month' => (new DateTime($purchase->date))->format('F Y'),
//                        'date' => (new DateTime($purchase->date))->format('d/m/y'),
                        'date' => $purchase->date,
                        'created_by' => optional($purchase->creator)->first_name . ' ' . optional($purchase->creator)->last_name,
                        'items' => implode('', $items->toArray()),
                        'item_ids' => json_encode($itemIds),
                        'total' => array_sum($totalItems),
                    ];
                });
        }
        catch(Exception $ex){

//            if (Auth::user()->roles()->first()->id === 17)
            return [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ];

        }

        return compact('purchaseRecords', 'monthBudget');
    }

    public function breviaryCallback(Request $request) {

        try {

            $data = $this->breviaryData($request);

            return [
                'count' => count($data['purchaseRecords']),
                'total' => number_format($data['purchaseRecords']->pluck('total')->sum(), 2),
                'monthBudget' => number_format($data['monthBudget'], 2),
            ];
        }
        catch(Exception $ex){

//            if (Auth::user()->roles()->first()->id === 17)
            return [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ];

        }
    }

    public function purchaseClassReclassify(Request $request)
    {
        if (!access()->allow('reclassify-purchases')) return redirect()->route('biller.dashboard');

        try {

            $purchaseClasses = PurchaseClass::orderBy('name')->whereHas('budgets.purchaseItems')->get();
            $expenseCategories = ExpenseCategory::orderBy('name')->whereHas('purchaseClass.budgets.purchaseItems')->get();

            if ($request->ajax()) {

                $purchaseRecords = $this->breviaryData($request)['purchaseRecords'];

                return Datatables::of($purchaseRecords)
                    ->addColumn('checkbox', function ($row) {
                        return '<input type="checkbox" class="row-checkbox" name="selected_items[]" value="' . $row['item_ids'] . '">';
                    })
                    ->editColumn('total', function ($model){

                        return number_format($model['total'], 2);
                    })
                    ->rawColumns(['items', 'p_number', 'checkbox'])
                    ->make(true);

            }

        }
        catch(Exception $ex){

            return [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ];

        }

        return view('focus.purchase_classes.reclassify', compact('purchaseClasses', 'expenseCategories'));
    }


    /**
     * @throws \DateMalformedStringException
     */
    public function reclassifyPurchases(Request $request) {

        if (!access()->allow('reclassify-purchases')) return redirect()->route('biller.dashboard');

        $validated = $request->validate([
            'purchase_class' => ['required', 'exists:purchase_classes,id'],
            'selected_items' => ['required', 'string'],
        ]);

        $item_ids = array_map(fn($i) => json_decode($i) , json_decode($validated['selected_items']));

        $purchaseItemIds = Arr::flatten($item_ids);

        $purchaseItems = PurchaseItem::whereIn('id', $purchaseItemIds)->with('purchase')->get();


        try {

            DB::beginTransaction();

            foreach ($purchaseItems as $item) {

                $purchase = $item->purchase;
                $itemName = $item->description;


                if ($purchase) {

                    $dpTid = gen4tid('DP-', $purchase->tid);

                    $purchaseDate = (new DateTime($purchase['date']))->format('Y-m-d');
                    $pcBudget = PurchaseClassBudget::where('purchase_class_id', $validated['purchase_class'])
                        ->whereHas('financialYear', function ($query) use ($purchase) {
                            $query->whereDate('start_date', '<=', $purchase->date)
                                ->whereDate('end_date', '>=', $purchase->date);
                        })
                        ->first();
    //
                    if (!$pcBudget) throw new Exception("The selected Non-Project Class for item '" . $itemName . "' on '" . $dpTid . "' has no Budget for the year wherein lies the purchase date...");

                    $item->purchase_class_budget = $pcBudget->id;
                    $item->save();
                }
                else throw new Exception( "The Purchase for Item '" . $itemName . ", could not be found!");

            }

            DB::commit();

            $pClass = PurchaseClass::find($validated['purchase_class']);

            return new RedirectResponse(route('biller.purchase-class-reclassify'), ['flash_success' => "Reclassified " . count($purchaseItemIds) . " Purchase " .  (count($purchaseItemIds) > 1 ? 'items' : 'item')  . " to '" . $pClass->name . "' purchase class!"]);
        }
        catch(Exception $ex){

            DB::rollBack();

            return errorHandler($ex->getMessage(), $ex);

//            return [
//                'message' => $ex->getMessage(),
//                'code' => $ex->getCode(),
//                'file' => $ex->getFile(),
//                'line' => $ex->getLine(),
//            ];

        }
    }
}
