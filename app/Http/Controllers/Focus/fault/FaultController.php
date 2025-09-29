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
namespace App\Http\Controllers\Focus\fault;

use App\Models\fault\Fault;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\fault\CreateResponse;
use App\Http\Responses\Focus\fault\EditResponse;
use App\Repositories\Focus\fault\FaultRepository;
use App\Models\department\Department;


/**
 * faultsController
 */
class FaultController extends Controller
{
    /**
     * variable to store the repository object
     * @var FaultRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param FaultRepository $repository ;
     */
    public function __construct(FaultRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\Fault\Request $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.fault.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateFaultRequestNamespace $request
     * @return \App\Http\Responses\Focus\fault\CreateResponse
     */
    public function create(Request $request)
    {
        return view('focus.fault.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorefaultRequestNamespace $request
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
        return new RedirectResponse(route('biller.faults.index'), ['flash_success' => 'Fault Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\fault\fault $fault
     * @param EditfaultRequestNamespace $request
     * @return \App\Http\Responses\Focus\fault\EditResponse
     */
    public function edit(Fault $fault, Request $request)
    {
        return new EditResponse($fault);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatefaultRequestNamespace $request
     * @param App\Models\fault\fault $fault
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, Fault $fault)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($fault, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.faults.index'), ['flash_success' => 'Fault Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletefaultRequestNamespace $request
     * @param App\Models\fault\fault $fault
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Fault $fault, Request $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($fault);
        //returning with successfull message
        return new RedirectResponse(route('biller.faults.index'), ['flash_success' => 'Fault Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletefaultRequestNamespace $request
     * @param App\Models\fault\fault $fault
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Fault $fault, Request $request)
    {
        //returning with successfull message
        return new ViewResponse('focus.fault.view', compact('fault'));
    }
    public function select(Request $request)
    {
        $q = $request->keyword;
        $users = Department::where('name', 'LIKE', '%'.$q.'%')
            ->limit(6)->get(['id', 'name']);

        return response()->json($users);
    }

}
