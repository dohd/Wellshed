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

use App\Models\employeesalary\EmployeeSalary;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\employeesalary\CreateResponse;
use App\Http\Responses\Focus\employeesalary\EditResponse;
use App\Repositories\Focus\employeesalary\EmployeeSalaryRepository;
use App\Http\Requests\Focus\employeesalary\ManageEmployeeSalaryRequest;
use App\Http\Requests\Focus\employeesalary\StoreEmployeeSalaryRequest;
use App\Models\hrm\Hrm;

/**
 * EmployeeSalariesController
 */
class EmployeeSalariesController extends Controller
{
    /**
     * variable to store the repository object
     * @var EmployeeSalaryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param EmployeeSalaryRepository $repository ;
     */
    public function __construct(EmployeeSalaryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\allowance\ManageEmployeeSalaryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageEmployeeSalaryRequest $request)
    {
        //$emplytest=Hrm::with(['monthlysalary'])->get();
       //dd($emplytest);
        return new ViewResponse('focus.employeesalary.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateAllowanceRequestNamespace $request
     * @return \App\Http\Responses\Focus\allowance\CreateResponse
     */
    public function create(StoreEmployeeSalaryRequest $request)
    {
        return new CreateResponse('focus.employeesalary.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreEmployeeSalaryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreEmployeeSalaryRequest $request)
    {
        //Input received from the request
        $input_salary = $request->except(['_token', 'ins','amount','allowance_deduction_category_id','is_taxable','type']);
        $input_allowance = $request->only(['amount', 'amount','allowance_deduction_category_id','is_taxable','type']);

        $input_salary['ins'] = auth()->user()->ins;
        $input_salary['created_by'] = auth()->user()->id;
        //Create the model using repository create method
        $this->repository->create(compact('input_salary', 'input_allowance'));
        //return with successfull message
        return new RedirectResponse(route('biller.employeesalaries.index'), ['flash_success' => trans('alerts.backend.allowance.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\department\Department $department
     * @param EditDepartmentRequestNamespace $request
     * @return \App\Http\Responses\Focus\department\EditResponse
     */
    public function edit(EmployeeSalary $employeesalary, StoreEmployeeSalaryRequest $request)
    {
        return new EditResponse($employeesalary);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateEmployeeSalaryRequestNamespace $request
     * @param App\Models\allowance\EmployeeSalary $employeesalary
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreEmployeeSalaryRequest $request, EmployeeSalary $employeesalary)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($employeesalary, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.employeesalary.index'), ['flash_success' => trans('alerts.backend.departments.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $department
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(EmployeeSalary $employeesalary, StoreEmployeeSalaryRequest $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($employeesalary);
        //returning with successfull message
        return new RedirectResponse(route('biller.employeesalary.index'), ['flash_success' => trans('alerts.backend.allowance.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $department
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(EmployeeSalary $employeesalary, ManageEmployeeSalaryRequest $request)
    {

        //returning with successfull message
        return new ViewResponse('focus.employeesalary.view', compact('employeesalary'));
    }

}
