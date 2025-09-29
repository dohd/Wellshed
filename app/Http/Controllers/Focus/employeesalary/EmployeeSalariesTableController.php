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
namespace App\Http\Controllers\Focus\employeesalary;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\employeesalary\EmployeeSalaryRepository;
use App\Http\Requests\Focus\employeesalary\ManageEmployeeSalaryRequest;

/**
 * Class DepartmentsTableController.
 */
class EmployeeSalariesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var EmployeeSalaryRepository
     */
    protected $employeesalary;

    /**
     * contructor to initialize repository object
     * @param EmployeeSalaryRepository $allowance ;
     */
    public function __construct(EmployeeSalaryRepository $employeesalary)
    {
        $this->employeesalary = $employeesalary;
    }

    /**
     * This method return the data of the model
     * @param ManageEmployeeSalaryRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageEmployeeSalaryRequest $request)
    {
        //
        $core = $this->employeesalary->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($employeesalary) {
                //  return $department->name;
                return '<a href="' . route('biller.employeesalaries.index') . '?rel_type=2&rel_id=' . $employeesalary->id . '">' . $employeesalary->name . '</a>';
            })
            ->addColumn('created_at', function ($employeesalary) {
                return dateFormat($employeesalary->created_at);
            })
            ->addColumn('actions', function ($employeesalary) {
                return '<a href="' . route('biller.employeesalaries.index') . '?rel_type=2&rel_id=' . $employeesalary->id . '" class="btn btn-purple round" data-toggle="tooltip" data-placement="top" title="List"><i class="fa fa-list"></i></a> ' . $employeesalary->action_buttons;
            })
            ->make(true);
    }
}
