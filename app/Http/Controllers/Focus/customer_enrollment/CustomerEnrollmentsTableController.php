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
namespace App\Http\Controllers\Focus\customer_enrollment;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\customer_enrollment\CustomerEnrollmentRepository;
use App\Http\Requests\Focus\customer_enrollment\Managecustomer_enrollmentRequest;

/**
 * Class CustomerEnrollmentsTableController.
 */
class CustomerEnrollmentsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var CustomerEnrollmentRepository
     */
    protected $customer_enrollment;

    /**
     * contructor to initialize repository object
     * @param CustomerEnrollmentRepository $customer_enrollment ;
     */
    public function __construct(CustomerEnrollmentRepository $customer_enrollment)
    {
        $this->customer_enrollment = $customer_enrollment;
    }

    /**
     * This method return the data of the model
     * @param Managecustomer_enrollmentRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->customer_enrollment->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($customer_enrollment) {
                $customer_name = '';
                if($customer_enrollment->customer)
                {
                    $customer_name = $customer_enrollment->customer->company ?? $customer_enrollment->customer->name;
                }else{
                    $customer_name = $customer_enrollment->name;
                }
                 return $customer_name;
            })
            ->addColumn('phone', function ($customer_enrollment) {
                $phone = '';
                if($customer_enrollment->customer)
                {
                    $phone = $customer_enrollment->customer->phone;
                }else{
                    $phone = $customer_enrollment->phone;
                }
                return $phone;
            })
            ->addColumn('email', function ($customer_enrollment) {
                 $email = '';
                if($customer_enrollment->customer)
                {
                    $email = $customer_enrollment->customer->email;
                }else{
                    $email = $customer_enrollment->email;
                }
                return $email;
            })
            ->addColumn('redeemable_code', function ($customer_enrollment) {
                 return $customer_enrollment->redeemable_code;
            })
            ->addColumn('created_at', function ($customer_enrollment) {
                return Carbon::parse($customer_enrollment->created_at)->toDateString();
            })
            ->addColumn('actions', function ($customer_enrollment) {
                return $customer_enrollment->action_buttons;
            })
            ->make(true);
    }
}
