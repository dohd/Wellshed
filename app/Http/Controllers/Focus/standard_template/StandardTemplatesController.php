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
namespace App\Http\Controllers\Focus\standard_template;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\standard_template\StandardTemplate;
use App\Repositories\Focus\standard_template\StandardTemplateRepository;

/**
 * StandardTemplatesController
 */
class StandardTemplatesController extends Controller
{
    /**
     * variable to store the repository object
     * @var standard_templateRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param standard_templateRepository $repository ;
     */
    public function __construct(StandardTemplateRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\standard_template\
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.standard_templates.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Createstandard_templateRequestNamespace $request
     * @return \App\Http\Responses\Focus\standard_template\CreateResponse
     */
    public function create()
    {
        return view('focus.standard_templates.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Storestandard_templateRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $data = $request->only(['name','description']);
        $data['ins'] = auth()->user()->ins;

        $data_items = $request->only(['product_id','unit_id','qty']);
        $data_items = modify_array($data_items);
        try {
            //Create the model using repository create method
            $this->repository->create(compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Creating Finished Product Template', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.standard_templates.index'), ['flash_success' => 'Finished Product Template Added Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\standard_template\standard_template $standard_template
     * @param Editstandard_templateRequestNamespace $request
     * @return \App\Http\Responses\Focus\standard_template\EditResponse
     */
    public function edit(StandardTemplate $standard_template)
    {
        return view('focus.standard_templates.edit', compact('standard_template'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Updatestandard_templateRequestNamespace $request
     * @param App\Models\standard_template\standard_template $standard_template
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, StandardTemplate $standard_template)
    {
        //Input received from the request
        $data = $request->only(['name','description']);

        $data_items = $request->only(['product_id','unit_id','qty','id']);
        $data_items = modify_array($data_items);
        try {
            //Update the model using repository update method
            $this->repository->update($standard_template, compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Finished Product Template', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.standard_templates.index'), ['flash_success' => 'Finished Product Template updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletestandard_templateRequestNamespace $request
     * @param App\Models\standard_template\standard_template $standard_template
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(StandardTemplate $standard_template)
    {
        try {
            //Calling the delete method on repository
            $this->repository->delete($standard_template);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error deleting Finished Product Template', $th);
        }
        //returning with successfull message
        return new RedirectResponse(route('biller.standard_templates.index'), ['flash_success' => 'Finished Product Templates Deleted Successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletestandard_templateRequestNamespace $request
     * @param App\Models\standard_template\standard_template $standard_template
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(StandardTemplate $standard_template)
    {

        //returning with successfull message
        return new ViewResponse('focus.standard_templates.view', compact('standard_template'));
    }

    public function get_std_templates(Request $request)
    {
        $templates = StandardTemplate::with('standard_template_items')->where('id', $request->template_id)->first();
        $items = $templates->standard_template_items->map(function($v){
            $v->product_name = @$v->product->name;
            $v->uom = @$v->unit->code;
            $v->code = @$v->product->code;
            return $v;
        });
        return response()->json($items);
    }

}
