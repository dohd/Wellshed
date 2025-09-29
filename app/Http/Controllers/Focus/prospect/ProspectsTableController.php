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

namespace App\Http\Controllers\Focus\prospect;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\prospect\ProspectRepository;

/**
 * Class BranchTableController.
 */
class ProspectsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $prospect;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(ProspectRepository $prospect)
    {

        $this->prospect = $prospect;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->prospect->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('title', function ($prospect) {

                $name = $prospect->title == null ? '-----' : $prospect->title;
                return $name;
            })
            ->addColumn('company', function ($prospect) {

                $name = $prospect->company == null ? '-----' : $prospect->company;
                return $name;
            })
            ->addColumn('name', function ($prospect) {

                $name = $prospect->contact_person == null ? '-----' : $prospect->contact_person;
                return $name;
            })
            // ->addColumn('email', function ($prospect) {
            //     $client_email = $prospect->email == null ? '-----' : $prospect->email ;

            //     return $client_email;
            // })
            ->addColumn('phone', function ($prospect) {
                $phone = $prospect->phone == null ? '-----' : $prospect->phone ;

                return $phone;
            })
            ->addColumn('industry', function ($prospect) {
                $client_industry = $prospect->industry == null ? '-----' : $prospect->industry;

                return $client_industry;
            })
            ->addColumn('region', function ($prospect) {
                $client_region = $prospect->region == null ? '-----': $prospect->region;

                return $client_region;
            })
            ->addColumn('temparate', function ($prospect) {
                $status = $prospect->temperate;
               

                return $status;
            })
            ->addColumn('call_status', function ($prospect) {
                $status = $prospect->call_status;
                if ($status == 'notcalled') {
                    $status = "Not called";
                } 
                else if ($status == 'callednotpicked'){
                    $status = "Called Not Picked";
                }
                else if($status == 'calledrescheduled') {
                    $status = "Call Rescheduled";
                }
                else if($status == 'callednotavailable') {
                    $status = "Called Not Available";
                }
                else  {
                    $status = "Called";
                }

                return $status;
            })
            ->addColumn('status', function ($prospect) {
                $status = $prospect->status;
                if ($status == 'open') {
                    $status = "Open";
                } else if ($status == 'won') {
                    $status = "Closed - Won";
                }else{
                    $status = "Closed - Lost";
                }

                return $status;
            })
            ->addColumn('reason', function ($prospect) {
                $reason = $prospect->reason == null ? '-----': $prospect->reason;

                return $reason;
            })
           
            ->addColumn('actions', function ($prospect) {
                return $prospect->action_buttons;
            })
            ->make(true);
    }

}
