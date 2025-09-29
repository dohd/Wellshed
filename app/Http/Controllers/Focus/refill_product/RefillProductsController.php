<?php

namespace App\Http\Controllers\Focus\refill_product;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\refill_product\RefillProduct;
use App\Models\refill_product_category\RefillProductCategory;
use App\Repositories\Focus\refill_product\RefillProductRepository;
use Illuminate\Http\Request;

class RefillProductsController extends Controller
{
    /**
     * variable to store the repository object
     * @var RefillProductRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param RefillProductRepository $repository ;
     */
    public function __construct(RefillProductRepository $repository)
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
        return view('focus.refill_products.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $productcategories = RefillProductCategory::get();

        return view('focus.refill_products.create', compact('productcategories'));
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
            return errorHandler('Error Creating Product', $th);
        }

        return new RedirectResponse(route('biller.refill_products.index'), ['flash_success' => 'Product Created Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(RefillProduct $refill_product)
    {
        return view('focus.refill_products.view', compact('refill_product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(RefillProduct $refill_product)
    {
        $productcategories = RefillProductCategory::get();

        return view('focus.refill_products.edit', compact('refill_product', 'productcategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RefillProduct $refill_product)
    {
        try {
            $this->repository->update($refill_product, $request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Product', $th);
        }

        return new RedirectResponse(route('biller.refill_products.index'), ['flash_success' => 'Product Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(RefillProduct $refill_product)
    {
        try {
            $this->repository->delete($refill_product);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Product', $th);
        }

        return new RedirectResponse(route('biller.refill_products.index'), ['flash_success' => 'Product Deleted Successfully']);
    }
}
