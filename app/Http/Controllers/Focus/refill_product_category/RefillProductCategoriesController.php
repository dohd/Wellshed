<?php

namespace App\Http\Controllers\Focus\refill_product_category;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\refill_product_category\RefillProductCategory;
use App\Repositories\Focus\refill_product_category\RefillProductCategoryRepository;
use Illuminate\Http\Request;

class RefillProductCategoriesController extends Controller
{
    /**
     * variable to store the repository object
     * @var RefillProductCategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param RefillProductCategoryRepository $repository ;
     */
    public function __construct(RefillProductCategoryRepository $repository)
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
        return view('focus.refill_product_categories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('focus.refill_product_categories.create');
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
            return errorHandler('Error Creating Product Category', $th);
        }

        return new RedirectResponse(route('biller.refill_product_categories.index'), ['flash_success' =>  'Product Category Created Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(RefillProductCategory $refill_product_category)
    {
        return view('focus.refill_product_categories.view', compact('refill_product_category'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(RefillProductCategory $refill_product_category)
    {
        return view('focus.refill_product_categories.edit', compact('refill_product_category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RefillProductCategory $refill_product_category)
    {
        try {
            $this->repository->update($refill_product_category, $request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Product Category', $th);
        }

        return new RedirectResponse(route('biller.refill_product_categories.index'), ['flash_success' => 'Product Category Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(RefillProductCategory $refill_product_category)
    {
        try {
            $this->repository->delete($refill_product_category);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Product Category', $th);
        }

        return new RedirectResponse(route('biller.refill_product_categories.index'), ['flash_success' => 'Product Category Deleted Successfully']);
    }
}
