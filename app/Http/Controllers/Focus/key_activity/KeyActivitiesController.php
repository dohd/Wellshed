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
namespace App\Http\Controllers\Focus\key_activity;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\key_activity\KeyActivity;
use Illuminate\Validation\ValidationException;

/**
 * KeyActivitiesController
 */
class KeyActivitiesController extends Controller
{
    

    /**
     * Display a listing of the resource.
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.key_activities.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateDepartmentRequestNamespace $request
     * @return \App\Http\Responses\Focus\department\CreateResponse
     */
    public function create()
    {
        return view('focus.key_activities.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreDepartmentRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            //Input received from the request
            $input = $request->except(['_token', 'ins']);
            $input['ins'] = auth()->user()->ins;
            KeyActivity::create($input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Creating Key Activity', $th);
        }
        
        //return with successfull message
        return new RedirectResponse(route('biller.key_activities.index'), ['flash_success' => 'Key Activity Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KeyActivity $key_activity)
    {
        return view('focus.key_activities.edit', compact('key_activity'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, KeyActivity $key_activity)
    {
        try {
            //Input received from the request
            $input = $request->except(['_token', 'ins']);
            //Update the model using repository update method
            $key_activity->update($input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Key Activity', $th);
        }
       
        //return with successfull message
        return new RedirectResponse(route('biller.key_activities.index'), ['flash_success' => 'Key Activity Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $department
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(KeyActivity $key_activity)
    {
        //Calling the delete method on repository
        try {
            if ($key_activity->subcategories()->exists()) throw ValidationException::withMessages(['Subcategories already exists']);
            $key_activity->delete();
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Deleting Key Activity', $th);
        }
        //returning with successfull message
        return new RedirectResponse(route('biller.key_activities.index'), ['flash_success' => 'Key Activity Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(KeyActivity $key_activity)
    {

        //returning with successfull message
        return new ViewResponse('focus.key_activities.view', compact('key_activity'));
    }

}
