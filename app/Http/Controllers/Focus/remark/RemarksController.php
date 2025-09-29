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

namespace App\Http\Controllers\Focus\remark;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\remark\CreateResponse;
use App\Http\Responses\Focus\remark\EditResponse;
use App\Repositories\Focus\remark\RemarkRepository;
use App\Http\Requests\Focus\remark\RemarkRequest;
use App\Models\branch\Branch;
use App\Models\remark\Remark;

/**
 * ProductcategoriesController
 */
class RemarksController extends Controller
{
    /**
     * variable to store the repository object
     * @var RemarkRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param RemarkRepository $repository ;
     */
    public function __construct(RemarkRepository $repository)
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

       
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {
        return new CreateResponse('focus.remarks.create');
    }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param StoreProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function store(RemarkRequest $request)   
    {

        // filter request input fields
        $data = $request->except(['_token', 'ins', 'files','temperate']);
        $temperate = $request->only(['temperate']);
       
        //Create the model using repository create method
        $this->repository->create(compact('data','temperate'));
        $remarks = Remark::where('prospect_id', $request->prospect_id)->orderBy('created_at', 'DESC')->limit(10)->get();
        return view('focus.prospects.partials.remarks_table', compact('remarks'));
    }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param \App\Models\remark\Remark $remark
    //  * @param EditProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\Focus\productcategory\EditResponse
    //  */
    public function edit(Remark $remark)
    {
        $branches = Branch::get(['id', 'name', 'customer_id']);


        return new EditResponse('focus.remarks.edit', compact('remark', 'branches'));
    }
    public function update(RemarkRequest $request,Remark $remark)
    {
      
        

        return new EditResponse('focus.remarks.edit', compact('remark'));
    }
}
