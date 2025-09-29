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

namespace App\Http\Controllers\Focus\client_vendor_tag;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\client_vendor_tag\ClientVendorTag;
use App\Repositories\Focus\client_vendor_tag\ClientVendorTagRepository;

class ClientVendorTagsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ClientVendorTagRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ClientVendorTagRepository $repository ;
     */
    public function __construct(ClientVendorTagRepository $repository)
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
        return new ViewResponse('focus.client_vendor_tags.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {   
        return view('focus.client_vendor_tags.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except(['_token']));
        } catch (\Throwable $th) { 
            return errorHandler('Error Creating Tag!', $th);
        }
        
        return new RedirectResponse(route('biller.client_vendor_tags.index'), ['flash_success' => 'Tag Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(ClientVendorTag $client_vendor_tag, Request $request)
    {
        return view('focus.client_vendor_tags.edit', compact('client_vendor_tag'));
    }

    /**
     * Update Resource in Storage
     * 
     */
    public function update(Request $request, ClientVendorTag $client_vendor_tag)
    {
        try {
            $this->repository->update($client_vendor_tag, $request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Tag!', $th);
        }
        
        return new RedirectResponse(route('biller.client_vendor_tags.index'), ['flash_success' => 'Tag Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(ClientVendorTag $client_vendor_tag)
    {
        try {
            $this->repository->delete($client_vendor_tag);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Tag!', $th);
        }

        return new RedirectResponse(route('biller.client_vendor_tags.index'), ['flash_success' => 'Tag Successfully Deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(ClientVendorTag $client_vendor_tag, Request $request)
    {
        return new ViewResponse('focus.client_vendor_tags.view', compact('client_vendor_tag'));
    }

    /**
     * Update Ticket Status
     * 
     */
    public function status(ClientVendorTag $client_vendor_tag, Request $request)
    {
        try {
            $client_vendor_tag->update(['status' => 'Closed', 'closed_at' => now()]);
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Status', $th);
        }
        return new RedirectResponse(route('biller.client_vendor_tags.index'), ['flash_success' => 'Tag Successfully Closed']);
    }
}
