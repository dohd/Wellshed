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
namespace App\Http\Controllers\Focus\pricegroup;

use App\Models\pricegroup\Pricegroup;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\pricegroup\CreateResponse;
use App\Http\Responses\Focus\pricegroup\EditResponse;
use App\Repositories\Focus\pricegroup\PricegroupRepository;
use App\Http\Requests\Focus\pricegroup\ManagePricegroupRequest;
use App\Http\Requests\Focus\pricegroup\StorePricegroupRequest;
use Illuminate\Validation\ValidationException;

/**
 * WarehousesController
 */
class PricegroupsController extends Controller
{
    /**
     * variable to store the repository object
     * @var WarehouseRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param WarehouseRepository $repository ;
     */
    public function __construct(PricegroupRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\warehouse\ManageWarehouseRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManagePricegroupRequest $request)
    {
        return new ViewResponse('focus.pricegroups.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateWarehouseRequestNamespace $request
     * @return \App\Http\Responses\Focus\warehouse\CreateResponse
     */
    public function create(StorePricegroupRequest $request)
    {

        return new CreateResponse('focus.pricegroups.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreWarehouseRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StorePricegroupRequest $request)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        //Create the model using repository create method
        $result = $this->repository->create($input);
        if (!$result) throw ValidationException::withMessages(['ref_id' => 'Duplicate Price Group is not allowed']);

        //return with successfull message
        return new RedirectResponse(route('biller.pricegroups.index'), ['flash_success' => trans('alerts.backend.pricegroups.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\warehouse\Warehouse $warehouse
     * @param EditWarehouseRequestNamespace $request
     * @return \App\Http\Responses\Focus\warehouse\EditResponse
     */
    public function edit(Pricegroup $pricegroup, StorePricegroupRequest $request)
    {
        return new EditResponse($pricegroup);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateWarehouseRequestNamespace $request
     * @param App\Models\warehouse\Warehouse $warehouse
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StorePricegroupRequest $request, Pricegroup $pricegroup)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($pricegroup, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.pricegroups.index'), ['flash_success' => trans('alerts.backend.pricegroups.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteWarehouseRequestNamespace $request
     * @param App\Models\warehouse\Warehouse $warehouse
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Pricegroup $pricegroup, StorePricegroupRequest $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($pricegroup);
        //returning with successfull message
        return new RedirectResponse(route('biller.pricegroups.index'), ['flash_success' => trans('alerts.backend.pricegroups.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteWarehouseRequestNamespace $request
     * @param App\Models\warehouse\Warehouse $warehouse
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Pricegroup $pricegroup, ManagePricegroupRequest $request)
    {

        //returning with successfull message
        return new ViewResponse('focus.pricegroups.view', compact('pricegroup'));
    }

}
