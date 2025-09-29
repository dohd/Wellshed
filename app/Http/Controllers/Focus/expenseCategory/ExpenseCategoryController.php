<?php

namespace App\Http\Controllers\Focus\expenseCategory;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\purchaseClass\ExpenseCategory;
use Closure;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

//        return ExpenseCategory::all();

        if (!access()->allow('manage-expense-category')) return redirect()->back();



        if ($request->ajax()) {

            $expenseCategories = ExpenseCategory::get();

            return Datatables::of($expenseCategories)

                ->addColumn('action', function ($model) {

                    $route = route('biller.expense-category.edit', $model->id);
                    $routeShow = route('biller.expense-category.show', $model->id);
                    $routeDelete = route('biller.expense-category.destroy', $model->id);

                    return '<a href="'.$route.'" class="btn btn-secondary round mr-1 mt-1">Edit</a>'
                        . '<a href="' .$routeDelete . '" 
                            class="btn btn-danger round mt-1" data-method="delete"
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


        return view('focus.expenseCategory.index',);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!access()->allow('create-expense-category')) return redirect()->back();

        return view('focus.expenseCategory.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {

        if (!access()->allow('create-expense-category')) return redirect()->back();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255',
                    function (string $attribute, $value, Closure $fail) {

                        $existingName = ExpenseCategory::where('name', $value)->first();

                        if ($existingName) {
                            $fail("You already have a Expense Category by the name '" . $value . "'.");
                        }
                    },
            ],
            'description' => ['required', 'string', 'max:2000']
        ]);

        $expenseCategory = new ExpenseCategory();
        $expenseCategory->fill($validated);

        $expenseCategory->save();

        return new RedirectResponse(route('biller.expense-category.index'), ['flash_success' => "Expense Category '" . $expenseCategory->name . "' saved successfully"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!access()->allow('edit-expense-category')) return redirect()->back();

        $expenseCategory = ExpenseCategory::find($id);

        return view('focus.expenseCategory.edit', compact('expenseCategory'));
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

        if (!access()->allow('edit-expense-category')) return redirect()->back();


        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255',
                    function (string $attribute, $value, Closure $fail) use($id) {

                        $existingName = ExpenseCategory::where('id', '!=', $id)
                            ->where('name', $value)->first();

                        if (!empty($existingName)) {
                            $fail("You already have a Expense Category by the name '" . $value . "'.");
                        }
                    },
            ],
            'description' => ['required', 'string', 'max:2000']
        ]);

        $expenseCategory = ExpenseCategory::find($id);
        $expenseCategory->fill($validated);
        $expenseCategory->save();

        return new RedirectResponse(route('biller.expense-category.index'), ['flash_success' => "Expense Category '" . $expenseCategory->name . "' updated successfully"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        if (!access()->allow('delete-expense-category')) return redirect()->back();

        $expenseCategory = ExpenseCategory::find($id);

        $name = $expenseCategory->name;

        if (count($expenseCategory->purchaseClass) > 0){
            return redirect()->route('biller.expense-category.index')
                ->with('flash_error', 'Delete Blocked as Expense Category is Allocated to ' . count($expenseCategory->purchaseClass) . ' Non-Project Class(es)');
        }

        $expenseCategory->delete();

        return new RedirectResponse(route('biller.expense-category.index'), ['flash_success' => "Expense Category '" . $name . "' deleted successfully"]);
    }
}
