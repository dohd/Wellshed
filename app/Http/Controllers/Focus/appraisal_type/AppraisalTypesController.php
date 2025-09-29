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
namespace App\Http\Controllers\Focus\appraisal_type;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\appraisal_type\AppraisalType;
use App\Repositories\Focus\appraisal_type\AppraisalTypeRepository;

/**
 * AppraisalTypesController
 */
class AppraisalTypesController extends Controller
{
    /**
     * variable to store the repository object
     * @var AppraisalTypeRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AppraisalTypeRepository $repository ;
     */
    public function __construct(AppraisalTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.appraisal_types.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateDepartmentRequestNamespace $request
     * @return \App\Http\Responses\Focus\department\CreateResponse
     */
    public function create()
    {
        return view('focus.appraisal_types.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreDepartmentRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        //Create the model using repository create method
        $this->repository->create($input);
        //return with successfull message
        return new RedirectResponse(route('biller.appraisal_types.index'), ['flash_success' => 'Appraisal Type Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \App\Http\Responses\Focus\department\EditResponse
     */
    public function edit(AppraisalType $appraisal_type)
    {
        return view('focus.appraisal_types.edit', compact('appraisal_type'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, AppraisalType $appraisal_type)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($appraisal_type, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.appraisal_types.index'), ['flash_success' => 'Appraisal Type Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(AppraisalType $appraisal_type)
    {
        //Calling the delete method on repository
        $this->repository->delete($appraisal_type);
        //returning with successfull message
        return new RedirectResponse(route('biller.appraisal_types.index'), ['flash_success' => 'Appraisal Type Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(AppraisalType $appraisal_type)
    {

        //returning with successfull message
        return new ViewResponse('focus.appraisal_types.view', compact('appraisal_type'));
    }

}
