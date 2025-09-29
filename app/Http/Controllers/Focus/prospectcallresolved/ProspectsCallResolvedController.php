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

namespace App\Http\Controllers\Focus\prospectcallresolved;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\prospectcallresolved\CreateResponse;
use App\Http\Responses\Focus\prospectcallresolved\EditResponse;
use App\Repositories\Focus\prospectcallresolved\ProspectCallResolvedRepository;
use App\Http\Requests\Focus\prospectcallresolved\ProspectCallResolvedRequest;
use App\Models\hrm\Hrm;
use App\Models\prospect_question\ProspectQuestion;
use App\Models\prospectcallresolved\ProspectCallResolved;


/**
 * ProductcategoriesController
 */
class ProspectsCallResolvedController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProspectRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProspectRepository $repository ;
     */
    public function __construct(ProspectCallResolvedRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        // $open_prospect = ProspectCallResolved::where('status', 0)->count();
        // $closed_prospect = ProspectCallResolved::where('status', 1)->count();
        // $total_prospect = ProspectCallResolved::count();
        $prospect_questions = ProspectQuestion::all();
        $users = Hrm::all();

        return new ViewResponse('focus.prospectscallresolved.index', compact('prospect_questions','users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    // public function create()
    // {
    //     return new CreateResponse('focus.prospects.create');
    //     //return view('focus.prospects.create', ['branches' => collect()]);
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param StoreProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function store(ProspectCallResolvedRequest $request)
    {

        // filter request input fields
        // dd($request->all());
        $data = $request->only(['prospect_id','prospect_question_id','temperate','any_remarks','reminder_date']);
        $data_items = $request->only(['question_id','answer_type','explanation']);
        $data_items = modify_array($data_items);
        $remarks = $request->only('prospect_id','recepient','any_remarks','reminder_date','prospect_question_id');
        try {
            //Create the model using repository create method
            $this->repository->create(compact('data','data_items','remarks'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error creating Prospect Call Resolved '.$th->getMessage(), $th);
        }

        return new RedirectResponse(route('biller.prospectscallresolved.index'), ['flash_success' => 'ProspectCallResolved Successfully Created']);
    }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param \App\Models\prospectcallresolved\ProspectCallResolved $prospectcallresolved
    //  * @param EditProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\Focus\productcategory\EditResponse
    //  */
    public function edit(ProspectCallResolved $prospectcallresolved)
    {
        return new EditResponse('focus.prospectscallresolved.edit', compact('prospectcallresolved'));
    }


    // follow up
    // public function followup(Request $request)
    // {
    //     $remarks = Remark::where('prospect_id', $request->id)->orderBy('updated_at', 'DESC')->limit(10)->get();
    //     return view('focus.prospects.partials.remarks_table', compact('remarks'));
    // }



    // /**
    //  * Update the specified resource.
    //  *
    //  * @param \App\Models\prospectcallresolved\ProspectCallResolved $prospectcallresolved
    //  * @param EditProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\Focus\productcategory\EditResponse
    //  */
    public function update(ProspectCallResolvedRequest $request, ProspectCallResolved $prospectcallresolved)
    {

        //dd($request);
        // update input fields from request
        $data = $request->only(['company', 'name', 'email', 'phone', 'region', 'industry']);

        //Update the model using repository update method
        $this->repository->update($prospectcallresolved, compact('data'));

        return new RedirectResponse(route('biller.prospectcallresolved.index'), ['flash_success' => 'ProspectCallResolved Successfully Updated']);
    }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param \App\Models\prospectcallresolved\ProspectCallResolved $prospectcallresolved
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function destroy(ProspectCallResolved $prospectcallresolved)
    {
        $this->repository->delete($prospectcallresolved);

        return new RedirectResponse(route('biller.prospectscallresolved.index'), ['flash_success' => 'ProspectCallResolved Successfully Deleted']);
    }

    // /**
    //  * Show the view for the specific resource
    //  *
    //  * @param DeleteProductcategoryRequestNamespace $request
    //  * @param \App\Models\prospectcallresolved\ProspectCallResolved $prospectcallresolved
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function show(ProspectCallResolved $prospectcallresolved)
    {
        return new ViewResponse('focus.prospectscallresolved.view', compact('prospectcallresolved'));
    }

    // /**
    //  * Update ProspectCallResolved Open Status
    //  */
    public function update_status(ProspectCallResolved $prospectcallresolved, Request $request)
    {

        $status = $request->status;
        $reason = $request->reason;
        $prospectcallresolved->update(compact('status', 'reason'));

        return redirect()->back();
    }

    public function notpicked(Request $request)
    {

        // filter request input fields
        $data = $request->except(['_token', 'ins', 'files']);
        $remarks = $request->only('prospect_id','any_remarks','reminder_date');
       
        
        //Create the model using repository create method
        $this->repository->notpickedcreate($data,$remarks);

        return new RedirectResponse(route('biller.prospectscallresolved.index'), ['flash_success' => 'ProspectCallResolved Successfully Created']);
    }
    public function notavailable(Request $request)
    {

        // filter request input fields
        $data = $request->except(['_token', 'ins', 'files']);
        $remarks = $request->only('prospect_id','any_remarks');
        
        //Create the model using repository create method
        $this->repository->notavailablecreate($data,$remarks);

        return new RedirectResponse(route('biller.prospectscallresolved.index'), ['flash_success' => 'ProspectCallResolved Successfully Created']);
    }
    public function pickedbusy(Request $request)
    {

        // filter request input fields
        $data = $request->except(['_token', 'ins', 'files','recepient']);

        $remarks = $request->only('prospect_id','recepient','any_remarks','reminder_date');
        
        //Create the model using repository create method
        $this->repository->pickedbusycreate($data,$remarks);

        return new RedirectResponse(route('biller.prospectscallresolved.index'), ['flash_success' => 'ProspectCallResolved Successfully Created']);
    }

    public function fetchprospectrecord(Request $request){
        $prospectid = $request->id;
        $resolve_id = $request->resolve_id;
        $prospectrecord = ProspectCallResolved::where('prospect_id',$prospectid)->where('id', $resolve_id)->get()[0];
        $items = $prospectrecord->items;

        
        
         return view('focus.prospects.partials.records_table', compact('prospectrecord','items'));
    }
}
