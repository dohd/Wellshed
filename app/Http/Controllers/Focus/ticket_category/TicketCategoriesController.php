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

namespace App\Http\Controllers\Focus\ticket_category;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\ticket_category\TicketCategory;
use App\Repositories\Focus\ticket_category\TicketCategoryRepository;
use Illuminate\Http\Request;

class TicketCategoriesController extends Controller
{
    /**
     * variable to store the repository object
     * @var TicketCategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param TicketCategoryRepository $repository ;
     */
    public function __construct(TicketCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.ticket_categories.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('focus.ticket_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    { 
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            errorHandler('Error Creating Ticket Category', $th);
        }

        return new RedirectResponse(route('biller.ticket_categories.index'), ['flash_success' => 'Ticket Category Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  TicketCategory $ticket_category
     * @return \Illuminate\Http\Response
     */
    public function edit(TicketCategory $ticket_category)
    {
        return view('focus.ticket_categories.edit', compact('ticket_category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  TicketCategory $ticket_category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TicketCategory $ticket_category)
    {
        try {
            $this->repository->update($ticket_category, $request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Ticket Category', $th);
        }

        return new RedirectResponse(route('biller.ticket_categories.index'), ['flash_success' => 'Ticket Category Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  TicketCategory $ticket_category
     * @return \Illuminate\Http\Response
     */
    public function destroy(TicketCategory $ticket_category)
    {
        try {
            $this->repository->delete($ticket_category);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Ticket Category', $th);
        }

        return new RedirectResponse(route('biller.ticket_categories.index'), ['flash_success' => 'Ticket Category Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  TicketCategory $ticket_category
     * @return \Illuminate\Http\Response
     */
    public function show(TicketCategory $ticket_category)
    {
        return view('focus.ticket_categories.view', compact('ticket_category'));
    }
}
