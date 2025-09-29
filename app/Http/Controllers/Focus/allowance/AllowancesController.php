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
namespace App\Http\Controllers\Focus\allowance;

use App\Models\allowance\Allowance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\allowance\CreateResponse;
use App\Http\Responses\Focus\allowance\EditResponse;
use App\Repositories\Focus\allowance\AllowanceRepository;
use App\Http\Requests\Focus\allowance\ManageAllowanceRequest;
use App\Http\Requests\Focus\allowance\StoreAllowanceRequest;


/**
 * AllowanceController
 */
class AllowancesController extends Controller
{
    /**
     * variable to store the repository object
     * @var AllowanceRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AllowanceRepository $repository ;
     */
    public function __construct(AllowanceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\allowance\ManageAllowanceRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageAllowanceRequest $request)
    {
        return new ViewResponse('focus.allowance.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateAllowanceRequestNamespace $request
     * @return \App\Http\Responses\Focus\allowance\CreateResponse
     */
    public function create(StoreAllowanceRequest $request)
    {
        return new CreateResponse('focus.allowance.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAllowanceRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreAllowanceRequest $request)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        //Create the model using repository create method
        $this->repository->create($input);
        //return with successfull message
        return new RedirectResponse(route('biller.allowances.index'), ['flash_success' => trans('alerts.backend.allowance.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\department\Department $department
     * @param EditDepartmentRequestNamespace $request
     * @return \App\Http\Responses\Focus\department\EditResponse
     */
    public function edit(Allowance $allowance, StoreAllowanceRequest $request)
    {
        return new EditResponse($allowance);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAllowanceRequestNamespace $request
     * @param App\Models\allowance\Allowance $allowance
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreAllowanceRequest $request, Allowance $allowance)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($allowance, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.allowances.index'), ['flash_success' => trans('alerts.backend.departments.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $department
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Allowance $allowance, StoreAllowanceRequest $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($allowance);
        //returning with successfull message
        return new RedirectResponse(route('biller.allowances.index'), ['flash_success' => trans('alerts.backend.allowance.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $department
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Allowance $allowance, ManageAllowanceRequest $request)
    {

        //returning with successfull message
        return new ViewResponse('focus.allowances.view', compact('allowance'));
    }

}
