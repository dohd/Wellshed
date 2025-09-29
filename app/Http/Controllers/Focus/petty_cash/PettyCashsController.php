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
namespace App\Http\Controllers\Focus\petty_cash;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\additional\Additional;
use App\Models\casual\CasualLabourer;
use App\Models\hrm\Hrm;
use App\Models\petty_cash\PettyCash;
use App\Models\petty_cash\PettyCashApproval;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Models\third_party_user\ThirdPartyUser;
use App\Repositories\Focus\petty_cash\PettyCashRepository;

/**
 * PettyCashsController
 */
class PettyCashsController extends Controller
{
    /**
     * variable to store the repository object
     * @var PettyCashRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param PettyCashRepository $repository ;
     */
    public function __construct(PettyCashRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\department\ManageDepartmentRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.petty_cashs.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateDepartmentRequestNamespace $request
     * @return \App\Http\Responses\Focus\department\CreateResponse
     */
    public function create()
    {
        $purchase_requisitions = PurchaseRequisition::where('status', 'approved')->orderBy('id','desc')->get();
        $additionals = Additional::all();
        $employees = Hrm::all();
        $third_party_users = ThirdPartyUser::all();
        $casuals = CasualLabourer::all();
        return view('focus.petty_cashs.create',compact('purchase_requisitions','additionals','employees','third_party_users','casuals'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreDepartmentRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store( Request $request)
    {
        //Input received from the request
        $data = $request->except(['_token', 'ins','qty','tax_rate','price','amount','product_id','product_name','uom','itemtax','approver_ids']);
        $data_items = $request->only(['qty','tax_rate','price','amount','product_id','product_name','uom','itemtax']);
        $data_items = modify_array($data_items);
        $approver_ids = implode(',',$request->input('approver_ids',[]));
        $data['approver_ids'] = $approver_ids;
        try {
            //Create the model using repository create method
            $this->repository->create(compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error creating Petty Cash!!', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.petty_cashs.index'), ['flash_success' => 'Petty Cash Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\department\Department $petty_cash
     * @param EditDepartmentRequestNamespace $request
     * @return \App\Http\Responses\Focus\department\EditResponse
     */
    public function edit(PettyCash $petty_cash)
    {
        $purchase_requisitions = PurchaseRequisition::where('status', 'approved')->orderBy('id','desc')->get();
        $additionals = Additional::all();
        $employees = Hrm::all();
        $third_party_users = ThirdPartyUser::all();
        $casuals = CasualLabourer::all();
        return view('focus.petty_cashs.edit',compact('petty_cash','purchase_requisitions','additionals','employees','third_party_users','casuals'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDepartmentRequestNamespace $request
     * @param App\Models\department\Department $petty_cash
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, PettyCash $petty_cash)
    {
        $data = $request->except(['_token', 'ins','qty','tax_rate','price','amount','product_id','product_name','uom','itemtax']);
        $data_items = $request->only(['qty','tax_rate','price','amount','product_id','product_name','uom','itemtax','id']);
        $data_items = modify_array($data_items);
        try {
            //Update the model using repository update method
            $this->repository->update($petty_cash, compact('data','data_items'));
        } catch (\Throwable $th) {dd($th);
            //throw $th;
            return errorHandler('Error Updationg Petty Cash!!', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.petty_cashs.index'), ['flash_success' => 'Petty Cash Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $petty_cash
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(PettyCash $petty_cash)
    {
        //Calling the delete method on repository
        $this->repository->delete($petty_cash);
        //returning with successfull message
        return new RedirectResponse(route('biller.petty_cashs.index'), ['flash_success' => 'Petty Cash Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $petty_cash
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(PettyCash $petty_cash)
    {

        //returning with successfull message
        return new ViewResponse('focus.petty_cashs.view', compact('petty_cash'));
    }

    public function change_status(Request $request, $petty_cash_id)
    {
        $data = $request->only(['status','status_note','date']);
        // dd($request->all());
        try {
            $data['petty_cash_id'] = $petty_cash_id;
            $data['date'] = date_for_database($data['date']);
            if($data['status'] == 'approved'){
                $data['approved_by'] = auth()->user()->id;
            }
            $petty_cash_approval = PettyCashApproval::create($data);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Changing Petty Cash Status',$th);
        }
        return new RedirectResponse(route('biller.petty_cashs.index'), ['flash_success' => 'Petty Cash Status Changed Successfully!!']);
    }

    public function index_petty_cash()
    {
        $employees = Hrm::all();
        $casuals = CasualLabourer::all();
        $third_party_users = ThirdPartyUser::all();
        return view('focus.petty_cashs.index_petty_cash',compact('employees','casuals','third_party_users'));
    }


}
