<?php

namespace App\Http\Controllers\Focus\PurchaseClassBudget;

use App\Http\Controllers\Controller;
use App\Models\PurchaseClassBudgets\PurchaseClassBudget;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PurchaseClassBudgetsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseClassBudget
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(PurchaseClassBudget $repository)
    {

        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $purchaseClassBudgets = PurchaseClassBudget::when(request('financial_year_id'), function ($q) {
            $q->where('financial_year_id', request('financial_year_id'));
        })->when(request('purchase_class_id'), function ($q) {
            $q->where('classlist_id', request('purchase_class_id'));
        })
        ->get();

        return Datatables::of($purchaseClassBudgets)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('purchaseClass', function ($model) {
                return @$model->purchaseClass->name;
            })
            ->addColumn('class', function ($model) {
                return @$model->classlist->name;
            })
            ->addColumn('expense_category', function ($model) {
                return @$model->purchaseClass->expenseCategory->name;
            })
            ->editColumn('financial_year_id', function ($model) {
                return @$model->financialYear->name;
            })
            ->addColumn('budget_sum', function ($model) {
                return $model->budget;
            })
            ->editColumn('budget', function ($model) {
                if (!$model->budget) return '';
                return '<p style="font-size: 18px;">' . numberFormat($model->budget) . '</p>';
            })
            ->addColumn('expense_to_date', function ($model) {
                $year = @$model->financialYear->start_date? date('Y', strtotime($model->financialYear->start_date)) : '';
                $purchases = 0;
                $purchaseOrders = 0;
                $purchases = $model->purchaseItems()->whereHas('purchase', fn ($q) => $q->whereYear('date', $year))->sum('amount');
                $purchaseOrders = $model->purchaseOrderItems()->whereHas('purchaseorder', fn ($q) => $q->whereYear('date', $year))->sum('amount'); 
                $expense = bcadd($purchases, $purchaseOrders, 2);
                $model->calculated_expense_to_date = $expense; // Store the expense to date
                if (+$model->budget) {
                    if ($expense > +$model->budget) return '<p style="color:red; font-size:18px;">' . numberFormat($expense) . '</p>';
                    return '<p style="color:green; font-size:18px;">' . numberFormat($expense) . '</p>';
                }
                return '<p style="font-size:18px;">' . numberFormat($expense) . '</p>';
            })
            ->addColumn('balance_to_date', function ($model) {
                $budget = +$model->budget;
                if ($budget) {
                    $expenseToDate = $model->calculated_expense_to_date ?: 0; // Retrieve the previously stored value
                    $balance = bcsub($budget, $expenseToDate, 2);
                    if ($balance < 0) return '<p style="color:red; font-size:18px;">' . numberFormat($balance) . '</p>';
                    return '<p style="color:green; font-size:18px;">' . numberFormat($balance) . '</p>';
                }
            })                    
            ->addColumn('action', function ($model) {
                return $model->action_buttons;
            })
            ->make(true);
    }
}