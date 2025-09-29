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

namespace App\Http\Controllers\Focus\holiday_list;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\holiday_list\HolidayList;
use App\Repositories\Focus\holiday_list\HolidayListRepository;
use Illuminate\Http\Request;

class HolidayListController extends Controller
{
    /**
     * variable to store the repository object
     * @var HolidayListRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param HolidayListRepository $repository ;
     */
    public function __construct(HolidayListRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.holiday_list.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('focus.holiday_list.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->repository->create($request->except('_token'));

        return new RedirectResponse(route('biller.holiday_list.index'), ['flash_success' => 'Holiday List Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  HolidayList $holiday_list
     * @return \Illuminate\Http\Response
     */
    public function edit(HolidayList $holiday_list)
    {
        return view('focus.holiday_list.edit', compact('holiday_list'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  HolidayList $holiday_list
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HolidayList $holiday_list)
    {
        $this->repository->update($holiday_list, $request->except('_token'));

        return new RedirectResponse(route('biller.holiday_list.index'), ['flash_success' => 'Holiday List Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  HolidayList $holiday_list
     * @return \Illuminate\Http\Response
     */
    public function destroy(HolidayList $holiday_list)
    {
        $this->repository->delete($holiday_list);

        return new RedirectResponse(route('biller.holiday_list.index'), ['flash_success' => 'Holiday List Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  HolidayList $holiday_list
     * @return \Illuminate\Http\Response
     */
    public function show(HolidayList $holiday_list)
    {
        return view('focus.holiday_list.view', compact('holiday_list'));
    }
}
