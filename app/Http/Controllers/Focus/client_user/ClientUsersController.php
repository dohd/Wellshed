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

namespace App\Http\Controllers\Focus\client_user;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\client_user\ClientUser;
use App\Repositories\Focus\client_user\ClientUserRepository;
use Illuminate\Validation\ValidationException;

class ClientUsersController extends Controller
{
    /**
     * variable to store the repository object
     * @var ClientUserRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ClientUserRepository $repository ;
     */
    public function __construct(ClientUserRepository $repository)
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
        return new ViewResponse('focus.client_users.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {   
        return view('focus.client_users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'password' => request('password') ? 'required_with:user_email | min:7' : '',
            'password_confirmation' => 'required_with:password | same:password',
        ]);
        if (request('password')) {
            if (!preg_match("/[a-z][A-Z]|[A-Z][a-z]/i", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain Upper and Lowercase letters']);
            if (!preg_match("/[0-9]/", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain At Least One Number']);
            if (!preg_match("/[^A-Za-z 0-9]/", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain A Symbol']);
        }
        
        try {
            $this->repository->create($request->except(['_token', 'password_confirmation']));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating User!', $th);
        }
        
        return new RedirectResponse(route('biller.client_users.index'), ['flash_success' => 'User Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(ClientUser $client_user, Request $request)
    {
        return view('focus.client_users.edit', compact('client_user'));
    }

    /**
     * Update Resource in Storage
     * 
     */
    public function update(Request $request, ClientUser $client_user)
    {
        $request->validate([
            'password' => request('password') ? 'required_with:user_email | min:7' : '',
            'password_confirmation' => 'required_with:password | same:password',
        ]);
        if (request('password')) {
            if (!preg_match("/[a-z][A-Z]|[A-Z][a-z]/i", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain Upper and Lowercase letters']);
            if (!preg_match("/[0-9]/", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain At Least One Number']);
            if (!preg_match("/[^A-Za-z 0-9]/", $request->password)) 
                throw ValidationException::withMessages(['password' => 'Password Must Contain A Symbol']);
        }

        try {
            $this->repository->update($client_user, $request->except(['_token', 'password_confirmation']));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating User!', $th);
        }
        
        return new RedirectResponse(route('biller.client_users.index'), ['flash_success' => 'User Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(ClientUser $client_user)
    {
        try {
            $this->repository->delete($client_user);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting User!', $th);
        }

        return new RedirectResponse(route('biller.client_users.index'), ['flash_success' => 'User Successfully Deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(ClientUser $client_user, Request $request)
    {
        return new ViewResponse('focus.client_users.view', compact('client_user'));
    }
}
