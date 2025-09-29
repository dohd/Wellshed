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
namespace App\Http\Controllers\Focus\third_party_user;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\third_party_user\ThirdPartyUser;
use App\Repositories\Focus\third_party_user\ThirdPartyUserRepository;

/**
 * ThirdPartyUsersController
 */
class ThirdPartyUsersController extends Controller
{
    /**
     * variable to store the repository object
     * @var ThirdPartyUserRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ThirdPartyUserRepository $repository ;
     */
    public function __construct(ThirdPartyUserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\department\ManageDepartmentRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.third_party_users.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateDepartmentRequestNamespace $request
     * @return \App\Http\Responses\Focus\department\CreateResponse
     */
    public function create()
    {
        return view('focus.third_party_users.create');
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
        return new RedirectResponse(route('biller.third_party_users.index'), ['flash_success' => 'Third Party User Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\department\Department $department
     * @param EditDepartmentRequestNamespace $request
     * @return \App\Http\Responses\Focus\department\EditResponse
     */
    public function edit(ThirdPartyUser $third_party_user)
    {
        return view('focus.third_party_users.edit', compact('third_party_user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDepartmentRequestNamespace $request
     * @param App\Models\department\Department $department
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, ThirdPartyUser $third_party_user)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($third_party_user, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.third_party_users.index'), ['flash_success' => 'Third Party User Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $department
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(ThirdPartyUser $third_party_user)
    {
        //Calling the delete method on repository
        $this->repository->delete($third_party_user);
        //returning with successfull message
        return new RedirectResponse(route('biller.third_party_users.index'), ['flash_success' => trans('alerts.backend.third_party_users.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $department
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(ThirdPartyUser $third_party_user)
    {
        //returning with successfull message
        return new ViewResponse('focus.third_party_users.view', compact('third_party_user'));
    }

}
