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
namespace App\Http\Controllers\Focus\lender;

use App\Http\Requests\Focus\general\CommunicationRequest;
use App\Models\account\Account;
use App\Models\lender\Lender;
use App\Models\transaction\TransactionHistory;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\lender\CreateResponse;
use App\Http\Responses\Focus\lender\EditResponse;
use App\Repositories\Focus\lender\LenderRepository;
use App\Http\Requests\Focus\lender\ManageLenderRequest;
use App\Http\Requests\Focus\lender\CreateLenderRequest;
use App\Http\Requests\Focus\lender\EditLenderRequest;
use App\Models\transaction\Transaction;
use DateTime;

/**
 * LendersController
 */
class LendersController extends Controller
{
    /**
     * variable to store the repository object
     * @var LenderRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param LenderRepository $repository ;
     */
    public function __construct(LenderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\customer\ManageLenderRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.lenders.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateLenderRequestNamespace $request
     * @return \App\Http\Responses\Focus\customer\CreateResponse
     */
    public function create(CreateLenderRequest $request)
    {

        return new CreateResponse('focus.lenders.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreLenderRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(CreateLenderRequest $request)
    {
        $request->validate([
            'name' => 'required',
            'contact' => 'required',
            
        ]);

        // extract input fields
        $input = $request->except(['_token', 'ins']);

               //Input received from the request
               
               $input['ins'] = auth()->user()->ins;
               $input['created_by'] = auth()->user()->id;
               //Create the model using repository create method
               $this->repository->create($input);
            
        return new RedirectResponse(route('biller.lenders.index'), ['flash_success' => 'Lender Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\customer\Lender $lender
     * @param EditLenderRequestNamespace $request
     * @return \App\Http\Responses\Focus\customer\EditResponse
     */
    public function edit(Lender $lender, EditLenderRequest $request)
    {
        return new EditResponse($lender);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateLenderRequestNamespace $request
     * @param App\Models\customer\Lender $lender
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(EditLenderRequest $request, Lender $lender)
    {
        $request->validate([
            'name' => 'required',
            'contact' => 'required',
            
        ]);
        // extract input fields
        $input = $request->except(['_token', 'ins']);
        
        $this->repository->update($lender, $input);

        return new RedirectResponse(route('biller.lenders.show', $lender), ['flash_success' => 'Lender Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Lender $lender)
    {
        $this->repository->delete($lender);

        return new RedirectResponse(route('biller.lenders.index'), ['flash_success' => 'Lender Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Lender $lender, ManageLenderRequest $request)
    {
        $params =  ['rel_type' => 9, 'rel_id' => $lender->id, 'system' => $lender->system];
        return new RedirectResponse(route('biller.lenders.index', $params), '');
       


    }

  

}
