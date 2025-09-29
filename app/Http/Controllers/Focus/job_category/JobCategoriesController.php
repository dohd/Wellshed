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
namespace App\Http\Controllers\Focus\job_category;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\job_category\CreateResponse;
use App\Http\Responses\Focus\job_category\EditResponse;
use App\Models\job_category\JobCategory;
use App\Repositories\Focus\job_category\JobCategoryRepository;

/**
 * JobCategoriesController
 */
class JobCategoriesController extends Controller
{
    /**
     * variable to store the repository object
     * @var JobCategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param JobCategoryRepository $repository ;
     */
    public function __construct(JobCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\job_category\Managejob_categoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.job_categories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Createjob_categoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\job_category\CreateResponse
     */
    public function create()
    {
        return new CreateResponse('focus.job_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Storejob_categoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        try {
            //Create the model using repository create method
            $this->repository->create($input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error creating job category', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.job-categories.index'), ['flash_success' => 'Job Category Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\job_category\job_category $job_category
     * @param Editjob_categoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\job_category\EditResponse
     */
    public function edit(JobCategory $job_category)
    {
        return new EditResponse($job_category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Updatejob_categoryRequestNamespace $request
     * @param App\Models\job_category\job_category $job_category
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, JobCategory $job_category)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        try {
            //Update the model using repository update method
            $this->repository->update($job_category, $input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error updating job category', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.job-categories.index'), ['flash_success' => 'Job Category Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletejob_categoryRequestNamespace $request
     * @param App\Models\job_category\job_category $job_category
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(JobCategory $job_category)
    {
        try {
            //Calling the delete method on repository
            $this->repository->delete($job_category);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error deleting job category', $th);
        }
        //returning with successfull message
        return new RedirectResponse(route('biller.job-categories.index'), ['flash_success' => 'Job Category deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletejob_categoryRequestNamespace $request
     * @param App\Models\job_category\job_category $job_category
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(JobCategory $job_category)
    {

        //returning with successfull message
        return new ViewResponse('focus.job_categories.view', compact('job_category'));
    }

}
