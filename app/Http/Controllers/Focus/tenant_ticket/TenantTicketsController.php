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

namespace App\Http\Controllers\Focus\tenant_ticket;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\tenant_service\TenantService;
use App\Models\tenant_ticket\TenantReply;
use App\Models\tenant_ticket\TenantTicket;
use App\Models\ticket_category\TicketCategory;
use App\Repositories\Focus\tenant_ticket\TenantTicketRepository;

class TenantTicketsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $repository ;
     */
    public function __construct(TenantTicketRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(Request $request)
    {
        return new ViewResponse('focus.tenant_tickets.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {   
        $services = TenantService::get();
        $categories = TicketCategory::where('module', 'Client Area')->get();
        
        return view('focus.tenant_tickets.create', compact('services', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {   
        $request->validate([
            'subject' => 'required',
            'message' => 'required',
        ]);

        try {
            $this->repository->create($request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Ticket!', $th);
        }
        
        return new RedirectResponse(route('biller.tenant_tickets.index'), ['flash_success' => 'Ticket  Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(TenantTicket $tenant_ticket, Request $request)
    {
        $services = TenantService::get();
        $categories = TicketCategory::where('module', 'Client Area')->get();

        return view('focus.tenant_tickets.edit', compact('tenant_ticket', 'services', 'categories'));
    }

    /**
     * Update Resource in Storage
     * 
     */
    public function update(Request $request, TenantTicket $tenant_ticket)
    {
        $request->validate([
            'subject' => 'required',
            'message' => 'required',
        ]);

        try {
            $this->repository->update($tenant_ticket, $request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Ticket!', $th);
        }
        
        return new RedirectResponse(route('biller.tenant_tickets.index'), ['flash_success' => 'Ticket  Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(TenantTicket $tenant_ticket)
    {
        try {
            $this->repository->delete($tenant_ticket);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Ticket!', $th);
        }

        return new RedirectResponse(route('biller.tenant_tickets.index'), ['flash_success' => 'Ticket  Successfully Deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(TenantTicket $tenant_ticket, Request $request)
    {
        return new ViewResponse('focus.tenant_tickets.view', compact('tenant_ticket'));
    }

    /**
     * Update Ticket Status
     * 
     */
    public function status(TenantTicket $tenant_ticket, Request $request)
    {
        try {
            $tenant_ticket->update(['status' => 'Closed', 'closed_at' => now()]);
        } catch (\Throwable $th) {
            return errorHandler('', $th);
        }
        return new RedirectResponse(route('biller.tenant_tickets.index'), ['flash_success' => 'Ticket  Successfully Closed']);
    }

    /**
     * Ticket Reply
     * 
     */
    public function reply(Request $request)
    {
        $request->validate(['message' => 'required']);
        try {
            $input = $request->only('tenant_ticket_id', 'message');
            $tenant_reply = TenantReply::create($input);
            if ($tenant_reply->tenant_ticket) {
                $tenant_reply->tenant_ticket->update(['status' => 'Open', 'closed_at' => null]);
            }
        } catch (\Throwable $th) {
            return errorHandler('', $th);
        }
        return redirect()->back();
    }
}
