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

namespace App\Http\Controllers\Focus\client_vendor_ticket;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\client_vendor_tag\ClientVendorTag;
use App\Models\client_vendor_ticket\ClientVendorReply;
use App\Models\client_vendor_ticket\ClientVendorTicket;
use App\Models\equipmentcategory\EquipmentCategory;
use App\Models\project\Project;
use App\Models\quote\Quote;
use App\Repositories\Focus\client_vendor_ticket\ClientVendorTicketRepository;

class ClientVendorTicketsController extends Controller
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
    public function __construct(ClientVendorTicketRepository $repository)
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
        return new ViewResponse('focus.client_vendor_tickets.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {   
        $equip_categories = EquipmentCategory::get(['id', 'name']);
        $tags = ClientVendorTag::get(['id', 'name']);

        return view('focus.client_vendor_tickets.create', compact('equip_categories','tags'));
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
            'category_id' => 'required',
            'priority' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);

        try {
            $this->repository->create($request->except(['_token']));
        } catch (\Throwable $th) { 
            return errorHandler('Error Creating Ticket!', $th);
        }
        
        return new RedirectResponse(route('biller.client_vendor_tickets.index'), ['flash_success' => 'Ticket  Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(ClientVendorTicket $client_vendor_ticket, Request $request)
    {
        $equip_categories = EquipmentCategory::get(['id', 'name']);
        $tags = ClientVendorTag::get(['id', 'name']);

        return view('focus.client_vendor_tickets.edit', compact('client_vendor_ticket', 'equip_categories', 'tags'));
    }

    /**
     * Update Resource in Storage
     * 
     */
    public function update(Request $request, ClientVendorTicket $client_vendor_ticket)
    {
        try {
            $this->repository->update($client_vendor_ticket, $request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Ticket!', $th);
        }
        
        return new RedirectResponse(route('biller.client_vendor_tickets.index'), ['flash_success' => 'Ticket  Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(ClientVendorTicket $client_vendor_ticket)
    {
        try {
            $this->repository->delete($client_vendor_ticket);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Ticket!', $th);
        }

        return new RedirectResponse(route('biller.client_vendor_tickets.index'), ['flash_success' => 'Ticket  Successfully Deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(ClientVendorTicket $client_vendor_ticket, Request $request)
    {
        $tags = ClientVendorTag::get(['id', 'name']);

        return new ViewResponse('focus.client_vendor_tickets.view', compact('client_vendor_ticket', 'tags'));
    }

    /**
     * Update Ticket Status
     * 
     */
    public function status(ClientVendorTicket $client_vendor_ticket, Request $request)
    {
        try {
            $client_vendor_ticket->update(['status' => 'Closed', 'closed_at' => now()]);
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Status', $th);
        }
        return new RedirectResponse(route('biller.client_vendor_tickets.index'), ['flash_success' => 'Ticket  Successfully Closed']);
    }

    /**
     * Update Ticket Progress Point
     */
    public function update_progress(ClientVendorTicket $client_vendor_ticket, Request $request)
    {
        try {
            $client_vendor_ticket->update(['tag_id' => $request->tag_id]);
            $tag = @$client_vendor_ticket->tag->name;
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Progress Status', $th);
        }
        return redirect()->back()->with('flash_success', $tag . ' Status Updated Successfully');
    }

    /**
     * Update Vendor Access
     */
    public function vendor_access(ClientVendorTicket $client_vendor_ticket, Request $request)
    {
        try {
            if ($client_vendor_ticket->vendor_access) $vendor_access = 0;
            else $vendor_access = 1;
            $client_vendor_ticket->update(compact('vendor_access'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Status', $th);
        }
        return redirect()->back()->with('flash_success', 'Vendor Access Updated Successfully');
    }

    /**
     * Cilient Ticket Reply
     */
    public function reply(Request $request)
    { 
        $request->validate(['message' => 'required']);
        try {
            $input = $request->only('client_vendor_ticket_id', 'message');
            $client_vendor_reply = ClientVendorReply::create($input);
            if ($client_vendor_reply->ticket) {
                $client_vendor_reply->ticket->update(['status' => 'Open', 'closed_at' => null]);
            }
        } catch (\Throwable $th) {
            return errorHandler('Error Replying Ticket!', $th);
        }
        
        return redirect()->back();
    }

    /**
     * Query Quotes
     */
    public function queryQuotes()
    {
        $t = request('search');
        $quotes = Quote::where('customer_id', auth()->user()->customer_id)
        ->where(function($q) use($t) {
            $q->where('tid', 'LIKE', '%'.$t.'%')->orWhere('notes', 'LIKE', '%'.$t.'%');
        })
        ->whereHas('customer', fn($q) => $q->where('company', 'LIKE', '%'.$t.'%')->orWhere('name', 'LIKE', '%'.$t.'%'))
        ->with('customer')
        ->limit(6)
        ->get(['id', 'tid', 'customer_id', 'branch_id', 'notes'])
        ->map(function($v) {
            $tid = gen4tid($v->bank_id? 'PI-' : 'QT-', $v->tid);
            $client = $v->customer->company ?: $v->customer->name; 
            $v['name'] = "[{$tid}] - {$client} - {$v->notes}";
            return $v;
        });

        return response()->json($quotes);
    }

    /**
     * Query Projects
     */
    public function queryProjects()
    {
        $t = request('search');
        $projects = Project::where('customer_id', auth()->user()->customer_id)
        ->where(function($q) use($t) {
            $q->where('tid', 'LIKE', '%'.$t.'%')->orWhere('name', 'LIKE', '%'.$t.'%');
        })
        ->whereHas('customer', fn($q) => $q->where('company', 'LIKE', '%'.$t.'%')->orWhere('name', 'LIKE', '%'.$t.'%'))
        ->with('customer')
        ->limit(6)
        ->get(['id', 'tid', 'customer_id', 'name'])
        ->map(function($v) {
            $tid = gen4tid('PRJ-', $v->tid);
            $client = $v->customer->company ?: $v->customer->name; 
            $v['name'] = "[{$tid}] - {$client} - {$v->name}";
            return $v;
        });

        return response()->json($projects);
    }
}
