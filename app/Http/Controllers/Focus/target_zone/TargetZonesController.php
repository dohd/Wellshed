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
namespace App\Http\Controllers\Focus\target_zone;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\target_zone\TargetZone;
use App\Repositories\Focus\target_zone\TargetZoneRepository;

/**
 * TargetZonesController
 */
class TargetZonesController extends Controller
{
    /**
     * variable to store the repository object
     * @var TargetZoneRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param TargetZoneRepository $repository ;
     */
    public function __construct(TargetZoneRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.target_zones.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        return view('focus.target_zones.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $data = $request->only(['name','description']);
        $data_items = $request->only(['sub_zone_name']);
        $data_items = modify_array($data_items);
        try {
            //Create the model using repository create method
            $this->repository->create(compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Creating Target Zone',$th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.target_zones.index'), ['flash_success' => 'Target Zone Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     */
    public function edit(TargetZone $target_zone)
    {
        return view('focus.target_zones.edit', compact('target_zone'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, TargetZone $target_zone)
    {
        // dd($request->all());
        //Input received from the request
        $data = $request->only(['name','description']);
        $data_items = $request->only(['sub_zone_name','id']);
        $data_items = modify_array($data_items);
        try {
            //Create the model using repository create method
           //Update the model using repository update method
        $this->repository->update($target_zone, compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Creating Target Zone',$th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.target_zones.index'), ['flash_success' => 'Target Zone Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param App\Models\target_zone\target_zone $target_zone
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(TargetZone $target_zone)
    {
        //Calling the delete method on repository
        $this->repository->delete($target_zone);
        //returning with successfull message
        return new RedirectResponse(route('biller.target_zones.index'), ['flash_success' => 'Target Zone Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletetarget_zoneRequestNamespace $request
     * @param App\Models\target_zone\target_zone $target_zone
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(TargetZone $target_zone)
    {

        //returning with successfull message
        return new ViewResponse('focus.target_zones.view', compact('target_zone'));
    }

}
