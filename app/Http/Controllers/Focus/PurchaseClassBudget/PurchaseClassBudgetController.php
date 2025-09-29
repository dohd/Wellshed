<?php

namespace App\Http\Controllers\Focus\PurchaseClassBudget;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\financial_year\FinancialYearController;
use App\Http\Requests\Focus\purchaseClass\PurchaseClassBudgetRequest;
use App\Http\Responses\RedirectResponse;
use App\Models\account\Account;
use App\Models\classlist\Classlist;
use App\Models\department\Department;
use App\Models\financialYear\FinancialYear;
use App\Models\items\PurchaseItem;
use App\Models\items\PurchaseorderItem;
use App\Models\product\ProductVariation;
use App\Models\purchase\Purchase;
use App\Models\purchaseClass\ExpenseCategory;
use App\Models\purchaseClass\PurchaseClass;
use App\Models\PurchaseClassBudgets\PurchaseClassBudget;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\supplier_product\SupplierProduct;
use Closure;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PurchaseClassBudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws Exception
     */


    public function index(Request $request)
    {
        if (!access()->allow('manage-purchase-class-budget')) return redirect()->back();

        $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        $expenseCategories = ExpenseCategory::whereHas('purchaseClass.budgets')->get();
        $departments = Department::whereHas('purchaseClassBudgets')->select('id', 'name')->get();

        $financialYears = FinancialYear::all(['id', 'name']);
        $purchaseClasses = PurchaseClass::orderBy('name')->whereHas('budgets')->select('id', 'name')->get();
        $classLists = Classlist::whereHas('purchase_class_budget')->select('id', 'name')->get();

        if ($request->ajax()) {
            try {
                //            if (empty($request->financial_year)) $purchaseClassBudgets = PurchaseClassBudget::all();
                //            else $purchaseClassBudgets = PurchaseClassBudget::where('financial_year_id', $request->financial_year)->get();

                $purchaseClassBudgets = PurchaseClassBudget::when(request('financial_year_filter'), function ($q) {
                    $q->where('financial_year_id', request('financial_year_filter'));
                })
                    ->when(request('purchase_class_filter'), function ($q) {
                        $q->where('purchase_class_id', request('purchase_class_filter'));
                    })
                    ->when(request('classlist_filter'), function ($q) {
                        $q->where('classlist_id', request('classlist_filter'));
                    })
                    ->when(request('expense_category_filter'), function ($q) {
                        $q->whereHas('purchaseClass.expenseCategory', function ($q) {
                            $q->where('id', request('expense_category_filter'));
                        });
                    })
                    ->get();


                return Datatables::of($purchaseClassBudgets)
                    ->addColumn('purchaseClass', function ($model) {

                        return $model->purchaseClass->name;
                    })

                    ->addColumn('expense_category', function ($model) {

                        $exCategory = $model->purchaseClass->expenseCategory;

                        return $exCategory ? $exCategory->name : '<b style="color: #FF8200"><i> Expense Category Not Set </i></b>';
                    })

                    ->editColumn('financial_year_id', function ($model) {

                        $fId = $model->financial_year_id;

                        if (!empty($fId)) return FinancialYear::find($model->financial_year_id)->name;
                        else return '<b style="color: #FF8200"><i> Financial Year Not Set </i></b>';
                    })

                    ->addColumn('department', function ($model) {

                        $dep = $model->classList;

                        if ($dep) return $dep->name;
                        else return '<b style="color: #0000D1"><i> non-Class List </i></b>';
                    })

                    ->addColumn('budget_sum', function ($model) {

                        return $model->budget;
                    })

                    ->editColumn('budget', function ($model) {

                        $budget = $model->budget;

                        if (!empty($budget)) return '<p style="font-size: 18px;">' . number_format($budget, 2) . '</p>';
                        else return '<b style="color: #FF8200"><i> Budget Not Set </i></b>';
                    })

                    ->addColumn('expense_to_date', function ($model) {

                        $purchases = 0.00;
                        $purchaseOrders = 0.00;
                        $budget = doubleval($model->budget);

                        if ($model->purchaseItems) {
                            $financialYear = (new DateTime($model->financialYear->start_date))->format('Y');

                            $purchasesItems = PurchaseItem::where('purchase_class_budget', $model->id)
                                ->select('item_id', 'type', 'amount')
                                ->whereHas('purchase', function ($q) use ($financialYear) {
                                    $q->whereYear('date', $financialYear);
                                })
                                ->get();

                            $purchases = $purchasesItems->sum('amount');
                        }

                        if ($model->purchaseOrderItems) {
                            $financialYear = (new DateTime($model->financialYear->start_date))->format('Y');

                            $purchaseOrdersItems = PurchaseorderItem::where('purchase_class_budget', $model->id)
                                ->select('item_id', 'type', 'amount')
                                ->whereHas('purchaseorder', function ($q) use ($financialYear) {
                                    $q->whereYear('date', $financialYear);
                                })
                                ->get();

                            $purchaseOrders = $purchaseOrdersItems->sum('amount');
                        }

                        $expense = bcadd($purchases, $purchaseOrders, 2);

                        $model->calculated_expense_to_date = $expense; // Store the expense to date

                        if (!empty(intval($budget))) {
                            if ($expense > $budget)
                                return '<p style="color: red; font-size: 18px;">' . number_format($expense, 2) . '</p>';
                            else
                                return '<p style="color: green; font-size: 18px;">' . number_format($expense, 2) . '</p>';
                        } else {
                            return '<p style="font-size: 18px;">' . number_format($expense, 2) . '</p>';
                        }
                    })

                    ->addColumn('balance_to_date', function ($model) {
                        if (empty($model->budget)) return '<b style="color: #FF8200"><i> Budget Not Set </i></b>';

                        $budget = doubleval($model->budget);
                        $expenseToDate = $model->calculated_expense_to_date ?? 0.00; // Retrieve the previously stored value

                        $balance = bcsub($budget, $expenseToDate, 2);

                        if ($balance < 0) return '<p style="color: red; font-size: 18px;">' . number_format($balance, 2) . '</p>';
                        else return '<p style="color: green; font-size: 18px;">' . number_format($balance, 2) . '</p>';
                    })                    ->addColumn('action', function ($model) {

                        $route = route('biller.purchase-class-budgets.edit', $model->id);
                        $routeShow = route('biller.purchase-class-budgets.show', $model->id);
                        $routeDelete = route('biller.purchase-class-budgets.destroy', $model->id);

                        return '<a href="' . $routeShow . '" class="btn btn-secondary round mr-1">Reports</a>'
                            . '<a href="' . $route . '" class="btn btn-twitter round mr-1">Edit</a>'
                            . '<a href="' . $routeDelete . '" 
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

                    ->rawColumns(['financial_year_id', 'month', 'budget', 'expense_to_date', 'balance_to_date', 'expense_to_date', 'action', 'expense_category', 'department'])
                    ->make(true);
            }
            catch (Exception $exception) {

                return [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];
            }
        }

        return view('focus.purchase_class_budgets.index', compact('purchaseClasses', 'classLists', 'financialYears', 'months', 'expenseCategories', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!access()->allow('create-purchase-class-budget')) return redirect()->back();

        $purchaseClasses = PurchaseClass::all()->map(function ($pc){
            return (object) [
                'id' => $pc->id,
                'name' => $pc->name . ' || Category: ' . (optional($pc->expenseCategory)->name ?? 'No Expense Category Selected'),
            ];
        });
        $financialYears = FinancialYear::all(['id', 'name']);
        $departments = Department::all(['id', 'name']);
        $classLists = Classlist::all();

        return view('focus.purchase_class_budgets.create',  compact('purchaseClasses','classLists', 'financialYears', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return RedirectResponse
     */
    public function store(PurchaseClassBudgetRequest $request)
    {
        $request->validate([
            'description' => 'required',
            'financial_year_id' => [
                function (string $attribute, $value, Closure $fail) use ($request) {
                    $existingFinancialYearBudget = PurchaseClassBudget::where('purchase_class_id', $request->purchase_class_id)
                        ->where('financial_year_id', $value)
                        ->where('department_id', $request->department_id)
                        ->first();

                    if ($existingFinancialYearBudget) {
                        $fail("There's an existing Budget for the Selected Non-Project Class & Department in the Selected Financial Year");
                    }
                },
            ],
        ]);
        $validated = $request->validated();

        try {
            $purchaseClassBudget = new PurchaseClassBudget();
            $purchaseClassBudget->fill($validated);
            $purchaseClassBudget->save();
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Expense Budget', $th);
        }

        return new RedirectResponse(route('biller.purchase-class-budgets.index'), ['flash_success' => "Expense Budget Created Successfully"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!access()->allow('manage-purchase-class-budget')) return redirect()->back();

        $purchaseClassBudget = PurchaseClassBudget::find($id);

        $purchases = PurchaseClassBudget::where('id', $id)
            ->with('purchaseItems.purchase.project', 'purchaseItems.purchase.budgetLine', 'purchaseItems.purchase.supplier', 'purchaseItems.purchase.creator')
            ->first();
        $purchaseOrders = PurchaseClassBudget::where('id', $id)
            ->with('purchaseOrderItems.purchaseorder.project', 'purchaseOrderItems.purchaseorder.budgetLine', 'purchaseOrderItems.purchaseorder.supplier', 'purchaseOrderItems.purchaseorder.creator')
            ->first();

        return view('focus.purchase_class_budgets.show', compact('purchaseClassBudget', 'purchases', 'purchaseOrders'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function edit($id)
    {
        if (!access()->allow('create-purchase-class-budget')) return redirect()->back();

        $purchaseClassBudget = PurchaseClassBudget::find($id);
        $financialYears = FinancialYear::all(['id', 'name']);
        $purchaseClasses = PurchaseClass::all()->map(function ($pc){
            return (object) [
                'id' => $pc->id,
                'name' => $pc->name . ' || Category: ' . (optional($pc->category)->name ?? 'No Expense Category Selected'),
            ];
        });
        $departments = Department::all(['id', 'name']);
        $classLists = Classlist::all();

        $financialYearMonths = [];
        $finYear = @$purchaseClassBudget->financial_year_id;
        if ($finYear) $financialYearMonths = (new FinancialYearController())->getFinancialYearMonths($finYear);

        return view('focus.purchase_class_budgets.edit', compact('purchaseClasses', 'classLists', 'purchaseClassBudget', 'financialYears', 'financialYearMonths', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return RedirectResponse
     * @throws \Exception
     */
    public function update(PurchaseClassBudgetRequest $request, $id)
    {
        $request->validate([
            'description' => 'required',
            'financial_year_id' => [
                function (string $attribute, $value, Closure $fail) use ($request, $id) {
                    $existingFinancialYearBudget = PurchaseClassBudget::where('id', '!=', $id)
                        ->where('purchase_class_id', $request->purchase_class_id)
                        ->where('financial_year_id', $value)
                        ->where('department_id', $request->department_id)
                        ->first();
                    if ($existingFinancialYearBudget) {
                        $fail("There's an existing Budget for the selected Non-Project Class & Department in the Selected Financial Year");
                    }
                },
            ],
        ]);
        $validated = $request->validated();

        try {
            $purchaseClassBudget = PurchaseClassBudget::find($id);
            $purchaseClassBudget->fill($validated);
            $purchaseClassBudget->save();
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Expense Budget', $th);
        }

        return new RedirectResponse(route('biller.purchase-class-budgets.index'), ['flash_success' => "Expense Budget Updated Successfully"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (!access()->allow('delete-purchase-class-budget')) return redirect()->back();

        $purchaseClassBudget = PurchaseClassBudget::where('id', $id)->with('purchaseItems.purchase', 'purchaseOrderItems.purchaseorder')->first();
        if ($purchaseClassBudget && count($purchaseClassBudget->purchaseItems)){
            return redirect()->back()->with('flash_error', 'Action Denied! Non-Project Class Budget is Allocated to ' . count($purchaseClassBudget->purchaseItems) . ' bills/expenses');
        } elseif ($purchaseClassBudget && count($purchaseClassBudget->purchaseOrderItems)) {
            return redirect()->back()->with('flash_error', 'Action Denied! Non-Project Class Budget is Allocated to ' . count($purchaseClassBudget->purchaseItems) . ' purchase orders');
        }

        try {
            $purchaseClassBudget = PurchaseClassBudget::find($id);
            $purchaseClassBudget->delete();
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Expense Budget', $th);
        }

        return new RedirectResponse(route('biller.purchase-class-budgets.index'), ['flash_success' => "Expense Budget Deleted successfully"]);
    }



    public function reportIndex(Request $request)
    {
        if ($request->ajax()) {
            $purchaseClassBudgets = PurchaseClassBudget::all();
            return Datatables::of($purchaseClassBudgets)
                ->addColumn('action', function ($model) {

                    $routeShow = route('biller.purchase-class-budgets.show', $model->id);

                    return '<a href="'.$routeShow.'" class="btn btn-secondary round mr-1">Reports</a>';
                })
                ->rawColumns(['action'])
                ->make(true);

        }

        return view('focus.purchase_class_budgets.reports');
    }

    public function getPurchasesData($purchaseClassBudgetId)
    {

        $purchases = Purchase::whereHas('products.purchaseClassBudget',function ($q) use($purchaseClassBudgetId) {
                $q->where('id', $purchaseClassBudgetId);
            })
            ->when(request('purchaseMonthFilter'), function ($q) {
                $q->whereMonth('date', intval(request('purchaseMonthFilter')))
                    ->whereYear('date', request('financialYear'));
            })
            ->get();

        return Datatables::of($purchases)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('p_number', function ($purchases) {

                return '<a href="'.route('biller.purchases.show', $purchases->id).'"><b>'. 'DP-' . str_pad($purchases->tid, 4, '0', STR_PAD_LEFT) .'</b></a>';
            })
            ->addColumn('supplier', function ($purchases) {
                return $purchases->supplier->name;
            })
            ->addColumn('month', function ($purchases) {
                return (new DateTime($purchases->date))->format('F Y');
            })
            ->addColumn('date', function ($purchases) {
                return (new DateTime($purchases->date))->format('d/m/y');
            })
            ->addColumn('created_by', function ($purchases) {
                return $purchases->creator->first_name . ' ' . $purchases->creator->last_name;
            })

            ->addColumn('item_names', function ($purchases) use ($purchaseClassBudgetId) {

                $items = PurchaseItem::where('bill_id', $purchases->id)
                    ->where('purchase_class_budget', $purchaseClassBudgetId)
                    ->select('item_id', 'type', 'amount')
                    ->get()
                    ->map(function ($pItem){

//                        $itemList = '';

                        if ($pItem->type === 'Stock'){

                            $name = '';

                            $productVariation = ProductVariation::find($pItem->item_id);
                            $supplierProduct = SupplierProduct::find($pItem->item_id);
                            if ($productVariation) $name = '<span> <b>Stock |</b>'. $productVariation->name . '<b> | Value: </b>' . number_format($pItem->amount, 2) . '</span><br>';
                            else $name = '<span> <b>Stock |</b>'. $supplierProduct->decr . '<b> | Value: </b>' . number_format($pItem->amount, 2) . '</span><br>';

//                            $itemList .= $name;
                        }
                        else if ($pItem->type === 'Expense') {

                            $name = '<span> <b>Expense |</b>'. optional(Account::find($pItem->item_id))->holder . '<b> | Value: </b>' . number_format($pItem->amount, 2) . '</span><br>';

//                            $itemList .= $name;
                        }

                        return $name;
                    });

                return implode('', $items->toArray());
            })

            ->addColumn('total', function ($purchases) use ($purchaseClassBudgetId) {

                $items = PurchaseItem::where('bill_id', $purchases->id)
                    ->where('purchase_class_budget', $purchaseClassBudgetId)
                    ->pluck('amount')
                    ->toArray();

                return number_format(array_sum($items), 2);
            })
            ->rawColumns(['item_names'])
            ->make(true);
    }


    public function getPurchasesMetrics()
    {

        try {

            $purchasesItems = PurchaseItem::where('purchase_class_budget', request('id'))
                ->select('item_id', 'type', 'amount')
                ->when(request('purchaseMonthFilter'), function ($q) {
                    $q->whereHas('purchase',function ($q) {
                        $q->whereMonth('date', intval(request('purchaseMonthFilter')))
                            ->whereYear('date', request('financialYear'));
                    });
                })
                ->get();


            $months = [1 => 'january', 2 => 'february', 3 => 'march', 4 => 'april', 5 => 'may', 6 => 'june', 7 => 'july', 8 => 'august', 9 => 'september', 10 => 'october', 11 => 'november', 12 => 'december'];
            if (request('purchaseMonthFilter')) {
                $pcbMonthBudget = PurchaseClassBudget::when(request('purchaseMonthFilter'), function ($q) {
                    $q->where('id', request('id'));
                })
                    ->first()
                    ->toArray()[$months[intval(request('purchaseMonthFilter'))]];

                $monthBudget = $pcbMonthBudget ?? '0.00';
            }
            else $monthBudget = '0.00';

            $purchaseItemsCount = count($purchasesItems);
            $purchaseItemsValue = $purchasesItems->sum('amount');
        }
        catch (Exception $ex){

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }


        return compact('purchaseItemsCount', 'purchaseItemsValue', 'monthBudget');
    }

    public function getPurchaseOrdersData($id){

        $purchaseOrders = Purchaseorder::whereHas('products.purchaseClassBudget',function ($q) use($id) {
                $q->where('id', $id);
            })
            ->when(request('purchaseOrderMonthFilter'), function ($q) {
                $q->whereMonth('date', intval(request('purchaseOrderMonthFilter')))
                    ->whereYear('date', request('financialYear'));
            })
            ->get();

        try {

            return Datatables::of($purchaseOrders)
                ->escapeColumns(['id'])
                ->addIndexColumn()
                ->addColumn('po_number', function ($purchaseOrders) {

                    return '<a href="'.route('biller.purchaseorders.show', $purchaseOrders->id).'"><b>'. 'PO-' . str_pad($purchaseOrders->tid, 4, '0', STR_PAD_LEFT) .'</b></a>';
                })
                ->addColumn('supplier', function ($purchaseOrders) {
                    return $purchaseOrders->supplier->name;
                })
                ->addColumn('month', function ($purchaseOrders) {
                    return (new DateTime($purchaseOrders->date))->format('F Y');
                })
                ->addColumn('date', function ($purchaseOrders) {
                    return (new DateTime($purchaseOrders->date))->format('d/m/y');
                })
                ->addColumn('created_by', function ($purchaseOrders) {
                    return $purchaseOrders->creator->first_name . ' ' . $purchaseOrders->creator->last_name;
                })

                ->addColumn('item_names', function ($purchaseOrders) use ($id) {

                    $items = PurchaseOrderItem::where('purchaseorder_id', $purchaseOrders->id)
                        ->where('purchase_class_budget', $id)
                        ->select('item_id', 'type', 'amount')
                        ->get()
                        ->map(function ($poItem){

//                        $itemList = '';

                            if ($poItem->type === 'Stock'){

                                $name = '';

                                $productVariation = ProductVariation::find($poItem->item_id);
                                $supplierProduct = SupplierProduct::find($poItem->item_id);
                                if ($productVariation) $name = '<span> <b>Stock |</b>'. $productVariation->name . '<b> | Value: </b>' . number_format($poItem->amount, 2) . '</span><br>';
                                else $name = '<span> <b>Stock |</b>'. $supplierProduct->decr . '<b> | Value: </b>' . number_format($poItem->amount, 2) . '</span><br>';

//                            $itemList .= $name;
                            }
                            else if ($poItem->type === 'Expense') {

                                $name = '<span> <b>Expense |</b>'. optional(Account::find($poItem->item_id))->holder . '<b> | Value: </b>' . number_format($poItem->amount, 2) . '</span><br>';

//                            $itemList .= $name;
                            }

                            return $name;
                        });

                    return implode('', $items->toArray());
                })

                ->addColumn('total', function ($purchaseOrders) use ($id){

                    $items = PurchaseOrderItem::where('purchaseorder_id', $purchaseOrders->id)
                        ->where('purchase_class_budget', $id)
                        ->pluck('amount')
                        ->toArray();

                    return number_format(array_sum($items), 2);
                })
                ->make(true);

        } catch (\Exception $e){

            return errorHandler("Error: '" . $e->getMessage() . " | on File: " . $e->getFile() . "  | & Line: " . $e->getLine());
        }
    }

    public function getPurchaseOrdersMetrics()
    {

        try {

            $purchaseOrdersItems = PurchaseOrderItem::where('purchase_class_budget', request('id'))
                ->select('item_id', 'type', 'amount')
                ->when(request('purchaseOrderMonthFilter'), function ($q) {
                    $q->whereHas('purchaseorder',function ($q) {
                        $q->whereMonth('date', intval(request('purchaseOrderMonthFilter')))
                            ->whereYear('date', request('financialYear'));
                    });
                })
                ->get();

            $purchaseOrdersCount = count($purchaseOrdersItems);
            $purchaseOrdersValue = $purchaseOrdersItems->sum('amount');

            $months = [1 => 'january', 2 => 'february', 3 => 'march', 4 => 'april', 5 => 'may', 6 => 'june', 7 => 'july', 8 => 'august', 9 => 'september', 10 => 'october', 11 => 'november', 12 => 'december'];
            if (request('purchaseOrderMonthFilter')) {
                $pcbMonthBudget = PurchaseClassBudget::when(request('purchaseOrderMonthFilter'), function ($q) {
                    $q->where('id', request('id'));
                })
                    ->first()
                    ->toArray()[$months[intval(request('purchaseOrderMonthFilter'))]];

                $monthBudget = $pcbMonthBudget ?? '0.00';
            }
            else $monthBudget = '0.00';

        }
        catch (Exception $ex){

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return compact('purchaseOrdersCount', 'purchaseOrdersValue', 'monthBudget');
    }

    /**
     * Non-Project Class Budget Metrics
     */
    public function metrics()
    {
        $purchaseClassBudgets = PurchaseClassBudget::when(request('financial_year_filter'), function ($q) {

                $q->where('financial_year_id', request('financial_year_filter'));
            })
            ->when(request('purchase_class_filter'), function ($q) {

                $q->where('purchase_class_id', request('purchase_class_filter'));
            })
            ->when(request('classlist_filter'), function ($q) {

                $q->where('classlist_id', request('classlist_filter'));
            })
            ->when(request('expense_category_filter'), function ($q) {

                $q->whereHas('purchaseClass', function ($r) {
                    $r->where('expense_category', request('expense_category_filter'));
                });
            })
            ->when(request('search_term'), function ($q) {

                $q->where(function ($q) {

                    $term = request('search_term');
                    $q->orWhereHas('financialYear', fn ($q) =>  $q->where('name', 'LIKE', '%' . $term . '%'))
                    ->orWhereHas('purchaseClass', fn ($q) => $q->where('name', 'LIKE', '%' . $term . '%'))
                    ->orWhereHas('purchaseClass.expenseCategory', fn ($q) => $q->where('name', 'LIKE', '%' . $term . '%'));
                });
            })
            ->get()
            ->map(function ($model) {

                $budget = +$model->budget;
                $year = @$model->financialYear->start_date? date('Y', strtotime($model->financialYear->start_date)) : '';

                $purchases = $model->purchaseItems()->whereHas('purchase', fn ($q) => $q->whereYear('date', $year))->sum('amount');

                $purchaseOrders = $model->purchaseOrderItems()->whereHas('purchaseorder', fn ($q) => $q->whereYear('date', $year))->sum('amount');

                return compact('budget', 'purchases', 'purchaseOrders');
            });

        $totalBudget = $purchaseClassBudgets->sum('budget');
        $totalPurchases = $purchaseClassBudgets->sum('purchases');
        $totalPurchaseOrders = $purchaseClassBudgets->sum('purchaseOrders');

        return compact('totalBudget', 'totalPurchases', 'totalPurchaseOrders');
    }


    public function chartMetrics($departmentId)
    {

        $donutTitle = "Non-Project Class Budget Distribution";
        $groupedBarTitle = "Non-Project Class Budget Distribution";
        $names = [];
        $budgets = [];
        $purchasesValue = 0.00;
        $purchaseOrdersValue = 0.00;

        if (empty($departmentId) || $departmentId == 'null') return compact('donutTitle', 'groupedBarTitle', 'names', 'budgets', 'purchasesValue', 'purchaseOrdersValue');

        try {
            $date = new DateTime();

            $today = (clone $date)->format('Y-m-d');
            $month = strtolower((clone $date)->format("F"));

            $department = Department::find($departmentId)->name;

            $donutTitle = "Non-Project Class Budget Distribution For the " . $department . " Department in " . (clone $date)->format("F") . " " . (clone $date)->format("Y");
            $groupedBarTitle = "Non-Project Class Budget & Expenditure on Purchases and Purchase Orders For the " . $department . " Department in " . (clone $date)->format("F") . " " . (clone $date)->format("Y");

            //        return
            $purchaseClassBudgetData = PurchaseClassBudget::
                whereHas('financialYear', function ($q) use ($today) {
                    $q->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today);
                })
                ->where('department_id', $departmentId)
                ->with(['purchaseItems' => function ($query) use ($today) {

                    $financialYear = FinancialYear::where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today)->first();

                    $query->whereHas('purchase', function ($q) use ($financialYear) {
                        $q->where('date', '>=', $financialYear->start_date)
                            ->where('date', '<=', $financialYear->end_date)
                            ->select('item_id', 'type', 'amount');
                    });

                }])
                ->with(['purchaseOrderItems' => function ($query) use ($today) {

                    $financialYear = FinancialYear::where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today)->first();

                    $query->whereHas('purchaseorder', function ($q) use ($financialYear) {
                        $q->where('date', '>=', $financialYear->start_date)
                            ->where('date', '<=', $financialYear->end_date)
                            ->select('item_id', 'type', 'amount');
                    });

                }])
                ->get();

            $budgets = $purchaseClassBudgetData->map(function ($pcb) use ($month) {
                return $pcb[$month];
            });

            $names = $purchaseClassBudgetData->map(function ($pcb) use ($month) {

                return PurchaseClass::find($pcb->purchase_class_id)->name;
            });

            //        return $purchaseClassBudgetData->toArray();

            $purchasesValue = [];
            $purchaseOrdersValue = [];

            foreach ($purchaseClassBudgetData->toArray() as $pcb) {

                $dpVal = 0.00;
                $poVal = 0.00;

                foreach ($pcb['purchase_items'] as $dpItem) $dpVal += doubleval($dpItem['amount']);
                array_push($purchasesValue, $dpVal);

                foreach ($pcb['purchase_order_items'] as $poItem) $poVal += doubleval($poItem['amount']);
                array_push($purchaseOrdersValue, $poVal);
            }
        }
        catch (Exception $ex){

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return compact('donutTitle', 'groupedBarTitle', 'names', 'budgets', 'purchasesValue', 'purchaseOrdersValue');
    }
}
