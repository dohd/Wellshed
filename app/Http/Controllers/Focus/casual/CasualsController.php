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
namespace App\Http\Controllers\Focus\casual;

use App\Models\casual\CasualLabourer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\casual\CreateResponse;
use App\Http\Responses\Focus\casual\EditResponse;
use App\Repositories\Focus\casual\CasualRepository;

/**
 * CasualsController
 */
class CasualsController extends Controller
{
    /**
     * variable to store the repository object
     * @var CasualRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param CasualRepository $repository ;
     */
    public function __construct(CasualRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\casual\ManagecasualRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.casuals.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatecasualRequestNamespace $request
     * @return \App\Http\Responses\Focus\casual\CreateResponse
     */
    public function create()
    {
        return new CreateResponse('focus.casuals.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorecasualRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'id_number' => 'required',
            'phone_number' => 'required',
            'gender' => 'required',
            'job_category_id' => 'required',
            'rate' => 'required',
            'work_type' => 'required',
        ]);
        
        $data = $request->except(['_token', 'ins','caption','document_name','existing_document_name','doc_id']);
        $data['ins'] = auth()->user()->ins;
        $data_items = $request->only(['caption','document_name','existing_document_name','doc_id']);
        $data_items = modify_array($data_items);
    
        try {
            $this->repository->create(compact('data','data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Casual Labourer', $th);
        }

        return new RedirectResponse(route('biller.casuals.index'), ['flash_success' => 'Casual Labourer Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\casual\casual $casual
     * @param EditcasualRequestNamespace $request
     * @return \App\Http\Responses\Focus\casual\EditResponse
     */
    public function edit(CasualLabourer $casual, Request $request)
    {
        return new EditResponse($casual);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatecasualRequestNamespace $request
     * @param App\Models\casual\casual $casual
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, CasualLabourer $casual)
    {
        $request->validate([
            'name' => 'required',
            'id_number' => 'required',
            'phone_number' => 'required',
            'gender' => 'required',
            'job_category_id' => 'required',
            'rate' => 'required',
            'work_type' => 'required',
        ]);

        $data = $request->except(['_token', 'ins','caption','document_name','existing_document_name','doc_id']);
        $data_items = $request->only(['caption','document_name','existing_document_name','doc_id']);
        $data_items = modify_array($data_items);

        try {
            $this->repository->update($casual, compact('data','data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Casual Labourer', $th);
        }

        return new RedirectResponse(route('biller.casuals.index'), ['flash_success' => 'Casual Labourer Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletecasualRequestNamespace $request
     * @param App\Models\casual\casual $casual
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(CasualLabourer $casual, Request $request)
    {
        try {
            $this->repository->delete($casual);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Casual Labourer', $th);
        }
        //returning with successfull message
        return new RedirectResponse(route('biller.casuals.index'), ['flash_success' => 'Casual Labourer Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletecasualRequestNamespace $request
     * @param App\Models\casual\casual $casual
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(CasualLabourer $casual, Request $request)
    {
        return new ViewResponse('focus.casuals.view', compact('casual'));
    }
}
