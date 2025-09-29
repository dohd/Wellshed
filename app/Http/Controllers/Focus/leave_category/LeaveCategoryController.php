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

namespace App\Http\Controllers\Focus\leave_category;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\leave_category\LeaveCategory;
use App\Repositories\Focus\leave_category\LeaveCategoryRepository;
use Illuminate\Http\Request;

class LeaveCategoryController extends Controller
{
    /**
     * variable to store the repository object
     * @var LeaveCategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param LeaveCategoryRepository $repository ;
     */
    public function __construct(LeaveCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.leave_category.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('focus.leave_category.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->repository->create($request->except('_token'));

        return new RedirectResponse(route('biller.leave_category.index'), ['flash_success' => 'Leave Category Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  LeaveCategory $leave_category
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveCategory $leave_category)
    {
        return view('focus.leave_category.edit', compact('leave_category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  LeaveCategory $leave_category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveCategory $leave_category)
    {
        $this->repository->update($leave_category, $request->except('_token'));

        return new RedirectResponse(route('biller.leave_category.index'), ['flash_success' => 'Leave Category Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  LeaveCategory $leave_category
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveCategory $leave_category)
    {
        $this->repository->delete($leave_category);

        return new RedirectResponse(route('biller.leave_category.index'), ['flash_success' => 'Leave Category Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  LeaveCategory $leave_category
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveCategory $leave_category)
    {
        return view('focus.leave_category.view', compact('leave_category'));
    }
}
