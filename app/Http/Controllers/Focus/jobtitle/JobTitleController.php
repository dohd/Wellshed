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
namespace App\Http\Controllers\Focus\jobtitle;

use App\Models\jobtitle\JobTitle;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\jobtitle\CreateResponse;
use App\Http\Responses\Focus\jobtitle\EditResponse;
use App\Repositories\Focus\jobtitle\JobTitleRepository;
use App\Models\department\Department;


/**
 * jobtitlesController
 */
class JobTitleController extends Controller
{
    /**
     * variable to store the repository object
     * @var JobTitleRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param JobTitleRepository $repository ;
     */
    public function __construct(JobTitleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\JobTitle\Request $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.jobtitle.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateJobTitleRequestNamespace $request
     * @return \App\Http\Responses\Focus\jobtitle\CreateResponse
     */
    public function create(Request $request)
    {
        return new CreateResponse('focus.jobtitle.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorejobtitleRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        //Input received from the request
        $input = $request->except(['_token']);
        $input['ins'] = auth()->user()->ins;
        $input['user_id'] = auth()->user()->id;
        //Create the model using repository create method
        $this->repository->create($input);
        //return with successfull messagetrans
        return new RedirectResponse(route('biller.jobtitles.index'), ['flash_success' => 'Job Title Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\jobtitle\jobtitle $jobtitle
     * @param EditjobtitleRequestNamespace $request
     * @return \App\Http\Responses\Focus\jobtitle\EditResponse
     */
    public function edit(JobTitle $jobtitle, Request $request)
    {
        return new EditResponse($jobtitle);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatejobtitleRequestNamespace $request
     * @param App\Models\jobtitle\jobtitle $jobtitle
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, JobTitle $jobtitle)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($jobtitle, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.jobtitles.index'), ['flash_success' => trans('alerts.backend.jobtitles.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletejobtitleRequestNamespace $request
     * @param App\Models\jobtitle\jobtitle $jobtitle
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(JobTitle $jobtitle, Request $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($jobtitle);
        //returning with successfull message
        return new RedirectResponse(route('biller.jobtitles.index'), ['flash_success' => trans('alerts.backend.jobtitles.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletejobtitleRequestNamespace $request
     * @param App\Models\jobtitle\jobtitle $jobtitle
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(JobTitle $jobtitle, Request $request)
    {
        //returning with successfull message
        return new ViewResponse('focus.jobtitle.view', compact('jobtitle'));
    }
    public function select(Request $request)
    {
        $q = $request->keyword;
        $users = Department::where('name', 'LIKE', '%'.$q.'%')
            ->limit(6)->get(['id', 'name']);

        return response()->json($users);
    }

}
