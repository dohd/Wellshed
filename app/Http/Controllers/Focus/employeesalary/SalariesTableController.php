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

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\hrm\HrmRepository;
use App\Http\Requests\Focus\employeesalary\ManageEmployeeSalaryRequest;
use Illuminate\Support\Facades\Storage;

/**
 * Class HrmsTableController.
 */
class SalariesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var HrmRepository
     */
    protected $hrm;
    /**
     * contructor to initialize repository object
     * @param HrmRepository $hrm ;
     */
    public function __construct(HrmRepository $hrm)
    {
        $this->hrm = $hrm;
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
        $core = $this->hrm->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($hrm) {
                if (request('rel_type') == 3) return '<a class="font-weight-bold" href="' . route('biller.transactions.index') . '?rel_type=3&rel_id=' . $hrm->id . '">' . $hrm->first_name . ' ' . $hrm->last_name . '</a> <small> ' . $hrm->phone . '</small>';
                return '<a class="font-weight-bold" href="' . route('biller.hrms.show', [$hrm->id]) . '">' . $hrm->first_name . ' ' . $hrm->last_name . '</a> <small> ' . $hrm->phone . '</small>';
            })
            ->addColumn('basic_salary', function ($hrm) {
                return isset($hrm->monthlysalary->salary) ? numberFormat($hrm->monthlysalary->salary + $hrm->monthlysalary->taxable_allowance) : '0';
            })
            ->addColumn('emp_no', function ($hrm) {
                $empno = sprintf('%04d', $hrm->meta->employee_no);
                $empno =  'EMP-' . $empno;
                return $empno;
            })
            ->addColumn('job_type', function ($hrm) {
                return isset($hrm->monthlysalary->employement_type) ? $hrm->monthlysalary->employement_type : '';
            })
            ->addColumn('paye', function ($hrm) {
                return isset($hrm->monthlysalary->paye) ? numberFormat($hrm->monthlysalary->paye) : '0';
            })
            ->addColumn('nhif', function ($hrm) {
                return isset($hrm->monthlysalary->nhif) ? numberFormat($hrm->monthlysalary->nhif) : '0';
            })
            ->addColumn('nssf', function ($hrm) {
                return isset($hrm->monthlysalary->nssf) ? numberFormat($hrm->monthlysalary->nssf) : '0';
            })
            ->addColumn('net_pay', function ($hrm) {
                return isset($hrm->monthlysalary->net_pay) ? numberFormat($hrm->monthlysalary->net_pay) : '0';
            })
            ->addColumn('deductions', function ($hrm) {
                return isset($hrm->monthlysalary->untaxable_allowance) ? numberFormat($hrm->monthlysalary->untaxable_allowance) : '0';
            })
            ->addColumn('effective_date', function ($hrm) {
                return isset($hrm->monthlysalary->effective_date) ? dateFormat($hrm->monthlysalary->effective_date) : '';
            })
            ->addColumn('contact_duration', function ($hrm) {
                return isset($hrm->monthlysalary->contact_duration) ? $hrm->monthlysalary->contact_duration . ' Months' : '';
            })
            ->addColumn('contact_end_date', function ($hrm) {
                return isset($hrm->monthlysalary->contact_end_date) ? dateFormat($hrm->monthlysalary->contact_end_date) : '';
            })
            ->addColumn('created_at', function ($hrm) {
                return dateFormat($hrm->created_at);
            })
            ->addColumn('actions', function ($hrm) {
                return $hrm->action_buttons;
            })
            ->make(true);
    }
}
