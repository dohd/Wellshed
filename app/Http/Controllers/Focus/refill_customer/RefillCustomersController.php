<?php

namespace App\Http\Controllers\Focus\refill_customer;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\refill_customer\RefillCustomer;
use App\Repositories\Focus\refill_customer\RefillCustomerRepository;
use Illuminate\Http\Request;

class RefillCustomersController extends Controller
{
    /**
     * variable to store the repository object
     * @var RefillCustomerRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param RefillCustomerRepository $repository ;
     */
    public function __construct(RefillCustomerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('focus.refill_customers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('focus.refill_customers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Customer', $th);
        }

        return new RedirectResponse(route('biller.refill_customers.index'), ['flash_success' => 'Customer Created Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(RefillCustomer $refill_customer)
    {
        return view('focus.refill_customers.view', compact('refill_customer'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(RefillCustomer $refill_customer)
    {
        return view('focus.refill_customers.edit', compact('refill_customer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RefillCustomer $refill_customer)
    {
        try {
            $this->repository->update($refill_customer, $request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Customer', $th);
        }

        return new RedirectResponse(route('biller.refill_customers.index'), ['flash_success' => 'Customer Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(RefillCustomer $refill_customer)
    {
        try {
            $this->repository->delete($refill_customer);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Customer', $th);
        }

        return new RedirectResponse(route('biller.refill_customers.index'), ['flash_success' => 'Customer Deleted Successfully']);
    }
}
