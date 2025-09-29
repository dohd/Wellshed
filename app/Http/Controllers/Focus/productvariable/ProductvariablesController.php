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
namespace App\Http\Controllers\Focus\productvariable;

use App\Http\Requests\Focus\general\ManageCompanyRequest;
use App\Models\productvariable\Productvariable;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\productvariable\CreateResponse;
use App\Http\Responses\Focus\productvariable\EditResponse;
use App\Repositories\Focus\productvariable\ProductvariableRepository;
use Illuminate\Validation\ValidationException;

/**
 * ProductvariablesController
 */
class ProductvariablesController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductvariableRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductvariableRepository $repository ;
     */
    public function __construct(ProductvariableRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productvariable\ManageProductvariableRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageCompanyRequest $request)
    {
        return new ViewResponse('focus.productvariables.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductvariableRequestNamespace $request
     * @return \App\Http\Responses\Focus\productvariable\CreateResponse
     */
    public function create(ManageCompanyRequest $request)
    {
        return new CreateResponse('focus.productvariables.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductvariableRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(ManageCompanyRequest $request)
    {
        $this->repository->create($request->except(['_token']));

        return new RedirectResponse(route('biller.productvariables.index'), ['flash_success' => 'Product Unit Variable Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productvariable\Productvariable $productvariable
     * @param EditProductvariableRequestNamespace $request
     * @return \App\Http\Responses\Focus\productvariable\EditResponse
     */
    public function edit(Productvariable $productvariable)
    {
        return new EditResponse($productvariable);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductvariableRequestNamespace $request
     * @param App\Models\productvariable\Productvariable $productvariable
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(ManageCompanyRequest $request, Productvariable $productvariable)
    {
        $this->repository->update($productvariable, $request->except(['_token']));

        return new RedirectResponse(route('biller.productvariables.index'), ['flash_success' => 'Product Unit Variable Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductvariableRequestNamespace $request
     * @param App\Models\productvariable\Productvariable $productvariable
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Productvariable $productvariable)
    {
        try {
            $this->repository->delete($productvariable);
        }
        catch (ValidationException $e) {
            // Return validation errors
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
         catch (\Throwable $th) {
            return errorHandler('Error Deleting UOM', $th);
         }
        
        
        return new RedirectResponse(route('biller.productvariables.index'), ['flash_success' => 'Product Unit Variable Successfully Deleted']);
    }

    /**
     * Show the specified resource.
     *
     * @param DeleteProductvariableRequestNamespace $request
     * @param App\Models\productvariable\Productvariable $productvariable
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Productvariable $productvariable, ManageCompanyRequest $request)
    {
        return new ViewResponse('focus.productvariables.view', compact('productvariable'));
    }
}
