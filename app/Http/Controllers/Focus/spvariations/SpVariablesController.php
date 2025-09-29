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
namespace App\Http\Controllers\Focus\spvariations;

use App\Models\pricegroup\PriceGroupVariation;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\spvariations\CreateResponse;
use App\Http\Responses\Focus\spvariations\EditResponse;
use App\Repositories\Focus\spvariations\SpVariableRepository;
use App\Http\Requests\Focus\spvariations\ManageSpVariableRequest;
use App\Http\Requests\Focus\spvariations\StoreSpVariableRequest;


/**
 * WarehousesController
 */
class SpVariablesController extends Controller
{
    /**
     * variable to store the repository object
     * @var SpVariableRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param SpVariableRepository $repository ;
     */
    public function __construct(SpVariableRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\spvariable\ManageWarehouseRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageSpVariableRequest $request)
    {
        $input = $request->only('rel_id');
        $rel_id = $request->rel_id;
        $segment = false;
        if ($rel_id) $segment = PriceGroupVariation::find($rel_id);            
        
        return new ViewResponse('focus.spvariations.index', compact('rel_id', 'segment', 'input'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateWarehouseRequestNamespace $request
     * @return \App\Http\Responses\Focus\spvariable\CreateResponse
     */
    public function create(StoreSpVariableRequest $request)
    {
        return new CreateResponse('focus.spvariables.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreWarehouseRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreSpVariableRequest $request)
    {

        $sprices = $request->only(['id','product_variation_id', 'product_id', 'selling_price']);
        $sprices['pricegroup_id'] = numberClean($request->input('pricegroup_id'));
        $sprices['ins'] = auth()->user()->ins;


      $result = $this->repository->create(compact('sprices'));
        return new RedirectResponse(route('biller.pricegroups.index'), ['flash_success' => 'Selling Price Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\warehouse\Warehouse $warehouse
     * @param EditWarehouseRequestNamespace $request
     * @return \App\Http\Responses\Focus\warehouse\EditResponse
     */
    public function edit(Warehouse $warehouse, StoreSpVariableRequest $request)
    {
        return new EditResponse($warehouse);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateWarehouseRequestNamespace $request
     * @param App\Models\warehouse\Warehouse $warehouse
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreSpVariableRequest $request, Warehouse $warehouse)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($warehouse, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.spvariables.index'), ['flash_success' => trans('alerts.backend.spvariables.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteWarehouseRequestNamespace $request
     * @param App\Models\warehouse\Warehouse $warehouse
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Warehouse $warehouse, StoreSpVariableRequest $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($warehouse);
        //returning with successfull message
        return new RedirectResponse(route('biller.spvariables.index'), ['flash_success' => trans('alerts.backend.spvariables.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteWarehouseRequestNamespace $request
     * @param App\Models\warehouse\PriceGroupVariable $PriceGroupVariable
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(PriceGroupVariable $PriceGroupVariable, ManageSpVariableRequest $request)
    {

        //returning with successfull message
        return new ViewResponse('focus.spvariables.view', compact('PriceGroupVariable'));
    }

}
