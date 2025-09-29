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
namespace App\Http\Controllers\Focus\customer_complain;

use App\Models\customer_complain\CustomerComplain;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\customer_complain\CreateResponse;
use App\Http\Responses\Focus\customer_complain\EditResponse;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Repositories\Focus\customer_complain\CustomerComplainRepository;


/**
 * customer_complainsController
 */
class CustomerComplainsController extends Controller
{
    /**
     * variable to store the repository object
     * @var CustomerComplainRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param CustomerComplainRepository $repository ;
     */
    public function __construct(CustomerComplainRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\customer_complain\Managecustomer_complainRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(Request $request)
    {
        return new ViewResponse('focus.customer_complains.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Createcustomer_complainRequestNamespace $request
     * @return \App\Http\Responses\Focus\customer_complain\CreateResponse
     */
    public function create(Request $request)
    {
        return new CreateResponse('focus.customer_complains.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Storecustomer_complainRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        $input['user_id'] = auth()->user()->id;
        $input['employees'] = json_encode($request->employees);
        $input['date'] = date_for_database($request->date);
        try {
            //Create the model using repository create method
            $this->repository->create($input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Creating Customer Complaint', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.customer_complains.index'), ['flash_success' => 'Customer Complaints Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\customer_complain\customer_complain $customer_complain
     * @param Editcustomer_complainRequestNamespace $request
     * @return \App\Http\Responses\Focus\customer_complain\EditResponse
     */
    public function edit(CustomerComplain $customer_complain, Request $request)
    {
        return new EditResponse($customer_complain);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Updatecustomer_complainRequestNamespace $request
     * @param App\Models\customer_complain\customer_complain $customer_complain
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, CustomerComplain $customer_complain)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['employees'] = json_encode($request->employees);
        $input['date'] = date_for_database($request->date);
        try {
            //Update the model using repository update method
            $this->repository->update($customer_complain, $input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error updating Customer Complaint', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.customer_complains.index'), ['flash_success' => 'Customer Complaints Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletecustomer_complainRequestNamespace $request
     * @param App\Models\customer_complain\customer_complain $customer_complain
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(CustomerComplain $customer_complain)
    {
        //Calling the delete method on repository
        $this->repository->delete($customer_complain);
        //returning with successfull message
        return new RedirectResponse(route('biller.customer_complains.index'), ['flash_success' => 'Customer Complaints Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletecustomer_complainRequestNamespace $request
     * @param App\Models\customer_complain\customer_complain $customer_complain
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(CustomerComplain $customer_complain, Request $request)
    {
        $employees = json_decode($customer_complain['employees']);
        $complain_to = [];
        if ($employees) {
            foreach ($employees as $employee) {
                $c = Hrm::where('id', $employee)->first();
                // dd($c->fullname);
                $d['a'] = $c->fullname;
                $complain_to[] = $d;
            }
        }
        //returning with successfull message
        return new ViewResponse('focus.customer_complains.view', compact('customer_complain', 'complain_to'));
    }

}
