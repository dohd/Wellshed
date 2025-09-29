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

namespace App\Http\Controllers\Focus\branch;

use App\Models\branch\Branch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\branch\CreateResponse;
use App\Http\Responses\Focus\branch\EditResponse;
use App\Repositories\Focus\branch\BranchRepository;
use App\Http\Requests\Focus\branch\ManageBranchRequest;
use App\Http\Requests\Focus\branch\StoreBranchRequest;
use App\Models\customer\Customer;
use Illuminate\Validation\ValidationException;

/**
 * ProductcategoriesController
 */
class BranchesController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $repository ;
     */
    public function __construct(BranchRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageBranchRequest $request)
    {

        // $core = $this->branch->getForDataTable();
        // dd($core );

        return new ViewResponse('focus.branches.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {
        return new CreateResponse('focus.branches.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreBranchRequest $request)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required'
        ]);
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        //Create the model using repository create method

        $id = $this->repository->create($input);
        //return with successfull message
        return new RedirectResponse(route('biller.branches.index'), ['flash_success' => 'Branch  Successfully Created' . ' <a href="' . route('biller.branches.show', [$id]) . '" class="ml-5 btn btn-outline-light round btn-min-width bg-blue"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> &nbsp; &nbsp;' . ' <a href="' . route('biller.branches.create') . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span> ' . trans('general.create') . '  </a>&nbsp; &nbsp;' . ' <a href="' . route('biller.branches.index') . '" class="btn btn-outline-blue round btn-min-width bg-amber"><span class="fa fa-list blue" aria-hidden="true"></span> <span class="blue">' . trans('general.list') . '</span> </a>']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Branch $branch, StoreBranchRequest $request)
    {
        //dd(0);
        return new EditResponse($branch);
    }

    /**
     *  Load customer branches for select options *  
     */
    public function select(Request $request)
    {
        $q = $request->search;
        $customer_id = $request->customer_id;

        $branches = Branch::where('customer_id', $customer_id)
            ->where('name', 'LIKE', '%'.$q.'%')
            ->limit(6)->get();
            
        return response()->json($branches);
    }

    public function update(StoreBranchRequest $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required'
        ]);
        //Input received from the request
        $input = $request->only(['name', 'rel_id', 'location', 'contact_name', 'contact_phone', 'branch_code']);
        //Update the model using repository update method
        $this->repository->update($branch, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.branches.index'), ['flash_success' => 'Branch  Successfully Updated'  . ' <a href="' . route('biller.branches.show', [$branch->id]) . '" class="ml-5 btn btn-outline-light round btn-min-width bg-blue"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> &nbsp; &nbsp;' . ' <a href="' . route('biller.branches.create') . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span> ' . trans('general.create') . '  </a>&nbsp; &nbsp;' . ' <a href="' . route('biller.branches.index') . '" class="btn btn-outline-blue round btn-min-width bg-amber"><span class="fa fa-list blue" aria-hidden="true"></span> <span class="blue">' . trans('general.list') . '</span> </a>']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Branch $branch)
    {
        try {
            $res = $this->repository->delete($branch);
            $params = ['flash_success' => 'Branch successfully deleted'];
            if (!$res) $params = ['flash_error' => 'Branch attached to Ticket'];
        }
        catch (ValidationException $e) {
            // Return validation errors
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
         catch (\Throwable $th) {
            return errorHandler('Error Deleting Branch', $th);
        }

        return new RedirectResponse(route('biller.branches.index'), $params);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(branch $branch, ManageBranchRequest $request)
    {
        return new ViewResponse('focus.branches.view', compact('branch'));
    }
}
