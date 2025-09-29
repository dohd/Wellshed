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
namespace App\Http\Controllers\Focus\customer_complain;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\customer_complain\CustomerComplainRepository;
use App\Http\Requests\Focus\customer_complain\Managecustomer_complainRequest;
use Illuminate\Http\Request;

/**
 * Class customer_complainsTableController.
 */
class CustomerComplainsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var customer_complainRepository
     */
    protected $customer_complain;

    /**
     * contructor to initialize repository object
     * @param customer_complainRepository $customer_complain ;
     */
    public function __construct(CustomerComplainRepository $customer_complain)
    {
        $this->customer_complain = $customer_complain;
    }

    /**
     * This method return the data of the model
     * @param Managecustomer_complainRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        //
        $core = $this->customer_complain->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('customer_name', function ($customer_complain) {
                $customer_name = '';
                if($customer_complain->client_feedback)
                {
                    $customer_name = $customer_complain->client_feedback->name;
                }else if($customer_complain->customer){
                    $customer_name = $customer_complain->customer->name ?? $customer_complain->customer->company;
                }
               return $customer_name;
            })
            ->addColumn('issue_description', function ($customer_complain) {
                
                return strip_tags($customer_complain->issue_description);
            })
            ->addColumn('solver', function ($customer_complain) {
                return optional($customer_complain->solver)->fullname;
            })
            ->addColumn('project', function ($customer_complain) {
                return optional(@$customer_complain->project)->name;
            })
            ->addColumn('date', function ($customer_complain) {
                return dateFormat($customer_complain->date);
            })
            ->addColumn('actions', function ($customer_complain) {
                return $customer_complain->action_buttons;
            })
            ->make(true);
    }
}
