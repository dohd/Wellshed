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
namespace App\Http\Controllers\Focus\salary;

use App\Models\Access\User\User;
use App\Models\hrm\Hrm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\salary\SalaryRepository;
//use App\Http\Requests\Focus\salary\ManagesalaryRequest;

/**
 * Class salarysTableController.
 */
class SalaryTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var salaryRepository
     */
    protected $salary;

    /**
     * contructor to initialize repository object
     * @param salaryRepository $salary ;
     */
    public function __construct(SalaryRepository $salary)
    {
        $this->salary = $salary;
    }

    /**
     * This method return the data of the model
     * @param ManagesalaryRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        //
        $core = $this->salary->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('employee_name', function ($salary) {
                return @$salary->user->full_name;
            })
            ->addColumn('basic_salary', function ($salary) {
                return amountFormat($salary->basic_salary);
            })
            ->addColumn('max_hourly_salary', function ($salary) {

                return amountFormat(bcmul($salary->hourly_salary, $salary->basic_salary));
            })
            ->addColumn('nhif', function ($salary) {
                return boolval($salary->nhif) ? 'Enrolled' : 'Exempt';
            })
            ->addColumn('actions', function ($salary) {
                return $salary->action_buttons;
            })
            ->make(true);
    }
}
