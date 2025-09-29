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
namespace App\Http\Controllers\Focus\deptor;

use App\Http\Requests\Focus\general\CommunicationRequest;
use App\Repositories\Focus\deptor\DeptorRepository;
use App\Http\Requests\Focus\deptor\ManageDeptorRequest;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\customer\Customer;
use App\Models\purchase\Purchase;
use App\Models\transaction\TransactionHistory;
//use App\Repositories\Focus\general\RosemailerRepository;
//use App\Repositories\Focus\general\RosesmsRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;

//use App\Http\Responses\Focus\customer\CreateResponse;
//use App\Http\Responses\Focus\customer\EditResponse;


//use App\Http\Requests\Focus\customer\CreateCustomerRequest;
//use App\Http\Requests\Focus\customer\EditCustomerRequest;
//use App\Http\Requests\Focus\customer\DeleteCustomerRequest;
//use Illuminate\Support\Facades\Mail;

/**
 * CustomersController
 */
class DeptorsController extends Controller
{
    /**
     * variable to store the repository object
     * @var CustomerRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param CustomerRepository $repository ;
     */
    public function __construct(DeptorRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\customer\ManageCustomerRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageDeptorRequest $request)
    {
        $input = $request->only('rel_type', 'rel_id');
        $segment = false;
         $customer = false;
              if (isset($input['rel_id']) and isset($input['rel_type'])) {
             $segment = Purchase::where('payer_id',$input['rel_id'])->where('payer_type','customer')->get();
              $customer = Customer::find($input['rel_id']);

           // $segment = CustomerGroupEntry::where('customer_group_id', '=', $input['rel_id'])->first();

        }
       
        return new ViewResponse('focus.deptors.index', compact('input', 'segment', 'customer'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateCustomerRequestNamespace $request
     * @return \App\Http\Responses\Focus\customer\CreateResponse
     */
    public function create( $request)
    {

        //return new CreateResponse('focus.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCustomerRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store( $request)
    {
        

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\customer\Customer $customer
     * @param EditCustomerRequestNamespace $request
     * @return \App\Http\Responses\Focus\customer\EditResponse
     */
    public function edit($request)
    {
        //return new EditResponse($customer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(  $customer)
    {
        

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy( $request)
    {

    }
        

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show( $request)
    {
        
    }

    
    

}
