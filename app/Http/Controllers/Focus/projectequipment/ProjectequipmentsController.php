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
namespace App\Http\Controllers\Focus\projectequipment;

use App\Models\projectequipment\Projectequipment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\projectequipment\CreateResponse;
use App\Http\Responses\Focus\projectequipment\EditResponse;
use App\Repositories\Focus\projectequipment\ProjectequipmentRepository;
use App\Http\Requests\Focus\projectequipment\ManageProjectequipmentRequest;
use App\Http\Requests\Focus\projectequipment\StoreProjectequipmentsRequest;


/**
 * ProductcategoriesController
 */
class ProjectequipmentsController extends Controller
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
    public function __construct(ProjectequipmentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageProjectequipmentRequest $request)
    {

        // $core = $this->branch->getForDataTable();
        // dd($core );

        return new ViewResponse('focus.projectequipments.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create(ManageProjectequipmentRequest $request)
    {

        return new CreateResponse('focus.projectequipments.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(ManageProjectequipmentRequest $request)
    {

         $data = $request->only(['shedule_id','client_id', 'project_id']);
         if (!empty($request->input('selected_products'))) {
         $data['equipment_id'] = explode(',', $request->input('selected_products'));
         $data['action'] = 1;
     }
       
         $data['ins'] = auth()->user()->ins;
         $data['loaded_by'] = auth()->user()->id;

          $transaction = $this->repository->create(compact('data'));

         echo json_encode(array('status' => 'Success', 'message' => trans('alerts.backend.equipments.created') . ' <a href="' . route('biller.jobschedules.index', [$returnvalue]) . '" class="btn btn-primary btn-md"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> &nbsp; &nbsp;'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Projectequipment $projectequipment, ManageProjectequipmentRequest $request)
    {
        
        return new EditResponse($projectequipment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */

     public function branch_load(Request $request)
    {
        $q = $request->get('id');
        $result = Branch::all()->where('rel_id', '=', $q);
        return json_encode($result);
    }


    public function update(ManageProjectequipmentRequest $request, Projectequipment $projectequipment)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required'
        ]);
        //Input received from the request
        $input = $request->only(['name', 'rel_id', 'location', 'contact_name', 'contact_phone']);
        //Update the model using repository update method
        $this->repository->update($branch, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.branches.index'), ['flash_success' => 'Branch  Successfully Updated'  . ' <a href="' . route('biller.branches.show', [$branch->id]) . '" class="ml-5 btn btn-outline-light round btn-min-width bg-blue"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> &nbsp; &nbsp;' . ' <a href="' . route('biller.branches.create') . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span> ' . trans('general.create') . '  </a>&nbsp; &nbsp;' . ' <a href="' . route('biller.branches.index') . '" class="btn btn-outline-blue round btn-min-width bg-amber"><span class="fa fa-list blue" aria-hidden="true"></span> <span class="blue">' . trans('general.list') . '</span> </a>']);

    }

    public function write_job_card(ManageProjectequipmentRequest $request)
    {




        $request->validate([
            'job_date' => 'required',
            'technician' => 'required',
            'job_card' => 'required'
           
        ]);


         $data = $request->only(['job_date','technician', 'job_card','recommendation']);
         if (!empty($request->input('selected_eqipment'))) {
         $data['equipment_id'] = explode(',', $request->input('selected_eqipment'));
         $data['action'] = 1;
     }

       
         $data['ins'] = auth()->user()->ins;
         $data['done_by'] = auth()->user()->id;



        $this->repository->job_card(compact('data'));



    echo json_encode(array('status' => 'Success', 'message' =>'Job Card Created Successfully'));
  

    }


    

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Projectequipment $projectequipment, ManageProjectequipmentRequest $request)
    {

        //dd($branch);
        //Calling the delete method on repository
        $this->repository->delete($projectequipment);
        //returning with successfull message
        return new RedirectResponse(route('biller.projectequipments.index'), ['flash_success' => 'Equipments Successfully Deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Projectequipment $region, ManageProjectequipmentRequest $request)
    {
        

        //returning with successfull message
        return new ViewResponse('focus.projectequipments.view', compact('projectequipment'));
        
    }

    public function load_region(Request $request)
    {
      
        $result = Region::all();
        return json_encode($result);
    }

}
