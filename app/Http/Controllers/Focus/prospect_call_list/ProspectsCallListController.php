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

namespace App\Http\Controllers\Focus\prospect_call_list;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\prospect\CreateResponse;
use App\Http\Responses\Focus\prospect\EditResponse;
use App\Repositories\Focus\prospect_call_list\ProspectCallListRepository;
use App\Http\Requests\Focus\prospect_call_list\ProspectCallListRequest;
use App\Models\prospect_calllist\ProspectCallList;

/**
 * ProductcategoriesController
 */
class ProspectCallListController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProspectCallList
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProspectCallListRepository $repository ;
     */
    public function __construct(ProspectCallListRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
     

        return view('focus.prospects.calllists.view');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {
        return new CreateResponse('focus.prospects.create');
        //return view('focus.prospects.create', ['branches' => collect()]);
    }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param StoreProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function store(ProspectCallListRequest $request)
    {

        // filter request input fields
        $data = $request->except(['_token', 'ins', 'files']);


        //Create the model using repository create method
        $this->repository->create($data);

        return new RedirectResponse(route('biller.prospects.index'), ['flash_success' => 'ProspectCallList Successfully Created']);
    }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param \App\Models\prospect\ProspectCallList $prospect
    //  * @param EditProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\Focus\productcategory\EditResponse
    //  */
    public function edit(ProspectCallList $prospectcalllist)
    {
        return new EditResponse('focus.prospects.edit', compact('prospectcalllist'));
    }


    // follow up
    // public function followup(Request $request)
    // {
    //     $remarks = Remark::where('prospect_id', $request->id)->orderBy('updated_at', 'DESC')->limit(10)->get();
    //     return view('focus.prospects.partials.remarks_table', compact('remarks'));
    // }



    // /**
    //  * Update the specified resource.
    //  *
    //  * @param \App\Models\prospect\ProspectCallList $prospect
    //  * @param EditProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\Focus\productcategory\EditResponse
    //  */
    public function update(ProspectCallListRequest $request, ProspectCallList $prospect)
    {

        //dd($request);
        // update input fields from request
        $data = $request->only(['company','name','email','phone','region','industry']);
        
        //Update the model using repository update method
        $this->repository->update();

        return new RedirectResponse(route('biller.prospects.index'), ['flash_success' => 'ProspectCallList Successfully Updated']);
    }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param \App\Models\prospect\ProspectCallList $prospect
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function destroy(ProspectCallList $prospectcalllist)
    {
        $this->repository->delete($prospectcalllist);

        return new RedirectResponse(route('biller.prospects.index'), ['flash_success' => 'ProspectCallList Successfully Deleted']);
    }

    // /**
    //  * Show the view for the specific resource
    //  *
    //  * @param DeleteProductcategoryRequestNamespace $request
    //  * @param \App\Models\prospect\ProspectCallList $prospect
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function show(ProspectCallList $prospect)
    {
        return new ViewResponse('focus.prospects.view', compact('prospect'));
    }

    // /**
    //  * Update ProspectCallList Open Status
    //  */
    public function update_status(ProspectCallList $prospect, Request $request)
    {

        $status = $request->status;
        $reason = $request->reason;
        $prospect->update(compact('status', 'reason'));

        return redirect()->back();
    }
    
}
