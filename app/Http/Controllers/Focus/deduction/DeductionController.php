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
namespace App\Http\Controllers\Focus\deduction;

use App\Models\deduction\Deduction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\deduction\CreateResponse;
use App\Http\Responses\Focus\deduction\EditResponse;
use App\Repositories\Focus\deduction\DeductionRepository;
use App\Models\department\Department;


/**
 * deductionsController
 */
class DeductionController extends Controller
{
    /**
     * variable to store the repository object
     * @var DeductionRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param deductionRepository $repository ;
     */
    public function __construct(DeductionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\deduction\Request $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.deduction.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatedeductionRequestNamespace $request
     * @return \App\Http\Responses\Focus\deduction\CreateResponse
     */
    public function create(Request $request)
    {
        return new CreateResponse('focus.deduction.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoredeductionRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        //Input received from the request
        $input = $request->only([
            'name','rate','amount_from','amount_to'
        ]);
        $user['ins'] = auth()->user()->ins;
        $user['user_id'] = auth()->user()->id;
        $input = modify_array($input);
        //Create the model using repository create method
        $this->repository->create(compact('user','input'));
        //return with successfull messagetrans
        return new RedirectResponse(route('biller.deductions.index'), ['flash_success' => 'Job Title Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\deduction\deduction $deduction
     * @param EditdeductionRequestNamespace $request
     * @return \App\Http\Responses\Focus\deduction\EditResponse
     */
    public function edit(Deduction $deduction, Request $request)
    {
        return new EditResponse($deduction);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatedeductionRequestNamespace $request
     * @param App\Models\deduction\deduction $deduction
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, deduction $deduction)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($deduction, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.deductions.index'), ['flash_success' => 'Statutory Deductions Updated!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletedeductionRequestNamespace $request
     * @param App\Models\deduction\deduction $deduction
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Deduction $deduction, Request $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($deduction);
        //returning with successfull message
        return new RedirectResponse(route('biller.deductions.index'), ['flash_success' => trans('alerts.backend.deductions.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletedeductionRequestNamespace $request
     * @param App\Models\deduction\deduction $deduction
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Deduction $deduction, Request $request)
    {
        //returning with successfull message
        return new ViewResponse('focus.deduction.view', compact('deduction'));
    }
    public function select(Request $request)
    {
        $q = $request->keyword;
        $users = Department::where('name', 'LIKE', '%'.$q.'%')
            ->limit(6)->get(['id', 'name']);

        return response()->json($users);
    }

}
