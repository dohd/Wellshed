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

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\projectequipment\ProjectequipmentRepository;
use App\Http\Requests\Focus\projectequipment\ManageProjectequipmentRequest;

/**
 * Class BranchTableController.
 */
class ProjectequipmentsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $projectequipment;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(ProjectequipmentRepository $projectequipment)
    {

        $this->projectequipment = $projectequipment;
    }

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageProjectequipmentRequest $request)
    {
    
if(request('job_card')==1){
        $core = $this->projectequipment->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()

             ->addColumn('mass_delete', function ($projectequipment) {
                    return  '<input type="checkbox" class="row-select" value="' . $projectequipment->id .'">' ;
                })

             ->addColumn('region', function ($projectequipment) {
                    return $projectequipment->region->name;
                })
             ->addColumn('branch', function ($projectequipment) {
                    return $projectequipment->branch->name;
                })
               ->addColumn('section', function ($projectequipment) {
                    return $projectequipment->section->name;
                })
                ->addColumn('location', function ($projectequipment) {
                    return $projectequipment->equipment->location;
                })
                 ->addColumn('equip_serial', function ($projectequipment) {
                    return $projectequipment->equipment->equip_serial;
                })
                  ->addColumn('manufacturer', function ($projectequipment) {
                    return $projectequipment->equipment->manufacturer;
                })
                    ->addColumn('model', function ($projectequipment) {
                    return $projectequipment->equipment->model;
                })
                      ->addColumn('category', function ($projectequipment) {
                    return $projectequipment->category->name;
                })
                       ->addColumn('related_equipments', function ($projectequipment) {
                    return $projectequipment->equipment->related_equipments;
                })
            ->addColumn('created_at', function ($projectequipment) {
                return dateFormat($projectequipment->created_at);
            })
          ->addColumn('status', function ($projectequipment) {
         if($projectequipment->status==0){
           return '<span class="badge" style="background-color:#12C538">Active</span>';
                }else{

                     return '<span class="badge" style="background-color:#f48fb1">InActive</span>';

                }

                })
            ->make(true);
        }else if(request('job_card')==2){

            $core = $this->projectequipment->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()

             ->addColumn('mass_delete', function ($projectequipment) {
                    return  '<input type="checkbox" class="row-select" value="' . $projectequipment->id .'">' ;
                })

             ->addColumn('region', function ($projectequipment) {
                    return $projectequipment->region->name;
                })
             ->addColumn('branch', function ($projectequipment) {
                    return $projectequipment->branch->name;
                })
               ->addColumn('section', function ($projectequipment) {
                    return $projectequipment->section->name;
                })
                ->addColumn('location', function ($projectequipment) {
                    return $projectequipment->equipment->location;
                })
                 ->addColumn('equip_serial', function ($projectequipment) {
                    return $projectequipment->equipment->equip_serial;
                })
                  ->addColumn('manufacturer', function ($projectequipment) {
                    return $projectequipment->equipment->manufacturer;
                })
                    ->addColumn('model', function ($projectequipment) {
                    return $projectequipment->equipment->model;
                })
                      ->addColumn('category', function ($projectequipment) {
                    return $projectequipment->category->name;
                })
                       ->addColumn('related_equipments', function ($projectequipment) {
                    return $projectequipment->equipment->related_equipments;
                })
            ->addColumn('created_at', function ($projectequipment) {
                return dateFormat($projectequipment->created_at);
            })
            ->addColumn('servicedate', function ($projectequipment) {
                return dateFormat($projectequipment->job_date);
            })
          ->addColumn('status', function ($projectequipment) {
         if($projectequipment->status==0){
           return '<span class="badge" style="background-color:#12C538">Active</span>';
                }else{

                     return '<span class="badge" style="background-color:#f48fb1">InActive</span>';

                }

                })
            ->make(true);


        }
    }
}
