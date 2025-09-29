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

namespace App\Http\Controllers\Focus\budget;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\project\Budget;
use App\Models\quote\Quote;
use App\Repositories\Focus\budget\BudgetRepository;
use Illuminate\Http\Request;

class BudgetsController extends Controller
{
    /**
     * variable to store the repository object
     * @var BudgetRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param BudgetRepository $repository ;
     */
    public function __construct(BudgetRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.leave.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $quote = Quote::find(request('quote_id'));

        return view('focus.budgets.create', compact('quote'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $budget = $this->repository->create($request->except('_token', 'files'));
            $project = @$budget->quote->project;
            if ($project) return new RedirectResponse(route('biller.projects.show', $project), ['flash_success' => 'Budget Created Successfully']);
            return new RedirectResponse(route('biller.budgets.index'), ['flash_success' => 'Budget Created Successfully']);
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Budget!', $th);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Budget $budget
     * @return \Illuminate\Http\Response
     */
    public function edit(Budget $budget)
    {
        $budget->load('items.product');
        $quote = $budget->quote;

        return view('focus.budgets.edit', compact('budget', 'quote'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Budget $budget
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Budget $budget)
    {
        try {
            $this->repository->update($budget, $request->except('_token', 'files'));
            $project = @$budget->quote->project;
            if ($project) return new RedirectResponse(route('biller.projects.show', $project), ['flash_success' => 'Budget Updated Successfully']);
            return new RedirectResponse(route('biller.budgets.index'), ['flash_success' => 'Budget Updated Successfully']);
        } catch (\Throwable $th) { dd($th);
            return errorHandler('Error Updating Budget!', $th);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Budget $budget
     * @return \Illuminate\Http\Response
     */
    public function destroy(Budget $budget)
    {
        try {
            $this->repository->delete($budget);
            return redirect()->back()->with('flash_success', "Budget Deleted Successfully");
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Budget!', $th);
        }        
    }


    /**
     * Display the specified resource.
     *
     * @param  Budget $budget
     * @return \Illuminate\Http\Response
     */
    public function show(Budget $budget)
    {
        $quote = $budget->quote;
        return view('focus.budgets.view', compact('budget','quote'));
    }
}
