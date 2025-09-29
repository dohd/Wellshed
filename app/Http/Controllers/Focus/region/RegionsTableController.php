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
namespace App\Http\Controllers\Focus\region;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\region\RegionRepository;
use App\Http\Requests\Focus\region\ManageRegionRequest;

/**
 * Class BranchTableController.
 */
class RegionsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $region;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(RegionRepository $region)
    {

        $this->region = $region;
    }

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageRegionRequest $request)
    {
    

        $core = $this->region->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
              
            ->addColumn('name', function ($region) {
                return '<a class="font-weight-bold" href="' . route('biller.regions.index') . '?rel_type=' . $region->id . '&rel_id=' . $region->id . '">' . $region->name . '</a>';
            })
        
            // ->addColumn('location', function ($branch) {
                    //return $branch->location;
              //  })
         
            ->addColumn('created_at', function ($region) {
                return dateFormat($region->created_at);
            })
            ->addColumn('actions', function ($region) {
                return $region->action_buttons;
               // return '<a class="btn btn-purple round" href="' . route('biller.branches.index') . '?rel_type=' . $branch->id . '&rel_id=' . $branch->id . '" title="List"><i class="fa fa-list"></i></a>' . $branch->action_buttons;
            })
            ->make(true);
    }
}
