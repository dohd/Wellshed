<?php

namespace App\Http\Controllers\Focus\product_refill;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\product_refill\ProductRefill;
use App\Models\refill_customer\RefillCustomer;
use App\Models\refill_product\RefillProduct;
use App\Repositories\Focus\product_refill\ProductRefillRepository;
use Illuminate\Http\Request;

class ProductRefillsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductRefillRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductRefillRepository $repository ;
     */
    public function __construct(ProductRefillRepository $repository)
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
        return view('focus.product_refills.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $refill_customers = RefillCustomer::get();
        $refill_products = RefillProduct::get();

        return view('focus.product_refills.create', compact('refill_customers', 'refill_products'));
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
            return errorHandler('Error Creating Refill', $th);
        }
        return new RedirectResponse(route('biller.product_refills.index'), ['flash_success' => 'Refill Created Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ProductRefill $product_refill)
    {
        return view('focus.product_refills.view', compact('product_refill'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductRefill $product_refill)
    {
        $refill_customers = RefillCustomer::get();
        $refill_products = RefillProduct::get();

        return view('focus.product_refills.edit', compact('product_refill', 'refill_customers', 'refill_products'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductRefill $product_refill)
    {
        try {
            $this->repository->update($product_refill, $request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Refill', $th);
        }

        return new RedirectResponse(route('biller.product_refills.index'), ['flash_success' => 'Refill Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductRefill $product_refill)
    {
        try {
            $this->repository->delete($product_refill);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Refill', $th);
        }

        return new RedirectResponse(route('biller.product_refills.index'), ['flash_success' => 'Refill Deleted Successfully']);
    }
}
