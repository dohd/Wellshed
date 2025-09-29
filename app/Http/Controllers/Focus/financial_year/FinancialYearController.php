<?php

namespace App\Http\Controllers\Focus\financial_year;

use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\financialYear\StoreFinancialYearRequest;
use App\Http\Requests\Focus\financialYear\UpdateFinancialYearRequest;
use App\Models\financialYear\FinancialYear;
use DateTime;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

/**
 *
 */
class FinancialYearController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function index(Request $request)
    {

        if (!access()->allow('manage-financial-year')) return redirect()->back();

        $financialYears = FinancialYear::all();

        if ($request->ajax()){

            return Datatables::of($financialYears)

                ->editColumn('start_date', function ($model) {


                    return (new DateTime($model->start_date))->format('jS F Y');
                })

                ->editColumn('end_date', function ($model) {


                    return (new DateTime($model->end_date))->format('jS F Y');
                })

                ->addColumn('budget', function ($fY) {

                    return number_format($fY->purchaseClassBudgets->pluck('budget')->sum(), 2);
                })

                ->addColumn('expenses', function ($fY) {

                    $purchaseItemsTotal = FinancialYear::where('id', $fY->id)
                        ->get()
                        ->map(function ($financialYear) {
                            return $financialYear->purchaseClassBudgets->map(function ($purchaseClassBudget) {
                                return $purchaseClassBudget->purchaseItems->sum('amount');
                            })->sum();
                        })
                        ->sum();

                    $purchaseOrderItemsTotal = FinancialYear::where('id', $fY->id)
                        ->get()
                        ->map(function ($financialYear) {
                            return $financialYear->purchaseClassBudgets->map(function ($purchaseClassBudget) {
                                return $purchaseClassBudget->purchaseOrderItems->sum('amount');
                            })->sum();
                        })
                        ->sum();

                    return number_format($purchaseItemsTotal + $purchaseOrderItemsTotal, 2);
                })

                ->addColumn('action', function ($model) {

                    $edit = ' <a href="' . route('biller.financial_years.edit',$model->id) . '" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="fa fa-pencil"></i></a> ';
                    $delete = '<a href="' . route('biller.financial_years.destroy',$model->id) . '" 
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

                    if(!access()->allow('edit-financial-year')) $edit = '';
                    if(!access()->allow('delete-financial-year')) $delete = '';

                    return $edit . $delete;
                })
                ->rawColumns(['action'])
                ->make(true);
        }


        return view('focus.financial_years.index', compact('financialYears'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        if (!access()->allow('create-financial-year')) return redirect()->back();

        return view('focus.financial_years.create');
    }

    /**
     * @param StoreFinancialYearRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function store(StoreFinancialYearRequest $request)
    {

        $validated = $request->validated();

        $financialYear = new FinancialYear($validated);
        $financialYear->name = 'Financial Year ' . (new DateTime($validated['start_date']))->format('jS M Y') . ' - ' . (new DateTime($validated['end_date']))->format('jS M Y');

        $financialYear->save();

        return redirect()->route('biller.financial_years.index')->with('success', 'Financial Year created successfully.');
    }

    /**
     * @param \App\Models\financialYear\FinancialYear $financialYear
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(FinancialYear $financialYear)
    {
        return view('focus.financial_years.show', compact('financialYear'));
    }

    /**
     * @param \App\Models\financialYear\FinancialYear $financialYear
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(FinancialYear $financialYear)
    {

        if (!access()->allow('edit-financial-year')) return redirect()->back();

        return view('focus.financial_years.edit', compact('financialYear'));
    }

    /**
     * @throws \Exception
     */
    public function update(UpdateFinancialYearRequest $request, FinancialYear $financialYear)
    {
        $validated = $request->validated();

        $financialYear->name = 'Financial Year ' . (new DateTime($validated['start_date']))->format('jS M Y') . ' - ' . (new DateTime($validated['end_date']))->format('jS M Y');
        $financialYear->update($validated);
        $financialYear->save();

        return redirect()->route('biller.financial_years.index')->with('success', 'Financial Year updated successfully.');
    }

    /**
     * @param \App\Models\financialYear\FinancialYear $financialYear
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(FinancialYear $financialYear)
    {

        if (!access()->allow('delete-financial-year')) return redirect()->back();

        if($financialYear->purchaseClassBudgets()->exists())
            return redirect()->back()->with('flash_error', 'Action Denied. Financial Year Has Related Records');

        $financialYear->delete();

        return redirect()->route('biller.financial_years.index')->with('success', 'Financial Year deleted successfully.');
    }

    /**
     * @throws \Exception
     */
    public function getFinancialYearMonths(int $financialYearId = 0): array{

        if (empty($financialYearId)) $financialYearId = request('financialYearId');

        $financialYear = FinancialYear::find($financialYearId);

        if(empty($financialYear)) return [];

        $financialYearStartMonth = (new DateTime($financialYear->start_date))->format('F');

        $monthsList = [];
        $date = new DateTime($financialYearStartMonth);

        for ($i = 0; $i < 12; $i++) {
            $monthsList[] = $date->format('F Y');
            $date->modify('+1 month');
        }

        return $monthsList;
    }

}
