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

namespace App\Http\Controllers\Focus\send_email;

use App\Models\send_email\SendEmail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\casual\CasualLabourer;
use App\Models\Company\Company;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\project\Project;
use App\Models\prospect\Prospect;
use App\Models\supplier\Supplier;
use App\Repositories\Focus\send_email\SendEmailRepository;


/**
 * SendEmailsController
 */
class SendEmailsController extends Controller
{
    /**
     * variable to store the repository object
     * @var SendEmailRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param SendEmailRepository $repository ;
     */
    public function __construct(SendEmailRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.send_emails.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        $employees = Hrm::all();
        $suppliers = Supplier::all();
        $customers = Customer::all();
        $labourers = CasualLabourer::all();
        $company = Company::find(auth()->user()->ins);
        $company_name = "From " . $company->sms_email_name . ': ';
        $prospect_industries = Prospect::get()->pluck('industry')->unique();
        $prospects = Prospect::all();
        $projects = Project::get()
            ->map(fn($v) => [
                'id' => $v->id,
                'name' => gen4tid('Prj-', $v->tid) . ' - ' . $v->name,
            ]);
        return view('focus.send_emails.create', compact('employees', 'suppliers', 'customers', 'labourers', 'company_name', 'prospects', 'prospect_industries','projects'));
    }

    /**
     * Store a newly created resource in storage.
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;

        try {
            //Create the model using repository create method
            $this->repository->create($input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Create send email ' . $th->getMessage(), $th);
        }
        return new RedirectResponse(route('biller.send_emails.index'), ['flash_success' => 'Send Email Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \App\Http\Responses\Focus\department\EditResponse
     */
    public function edit(SendEmail $send_email)
    {
        $employees = Hrm::all();
        $suppliers = Supplier::all();
        $customers = Customer::all();
        $labourers = CasualLabourer::all();
        $company = Company::find(auth()->user()->ins);
        $company_name = "From " . $company->sms_email_name . ': ';
        $prospects = Prospect::all();
        $prospect_industries = Prospect::get()->pluck('industry')->unique();
        $projects = Project::get()
            ->map(fn($v) => [
                'id' => $v->id,
                'name' => gen4tid('Prj-', $v->tid) . ' - ' . $v->name,
            ]);
        return view('focus.send_emails.edit', compact('send_email', 'employees', 'suppliers', 'customers', 'labourers', 'company_name', 'prospects', 'prospect_industries','projects'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, SendEmail $send_email)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($send_email, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.send_emails.index'), ['flash_success' => 'Send Email Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(SendEmail $send_email)
    {
        //Calling the delete method on repository
        $this->repository->delete($send_email);
        //returning with successfull message
        return new RedirectResponse(route('biller.send_emails.index'), ['flash_success' => 'Send Email Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show($send_email_id)
    {
        $send_email = SendEmail::find($send_email_id);
        $user_ids = explode(',', $send_email->user_ids);
        $users = [];
        foreach ($user_ids as $user_id) {
            if ($send_email->user_type == 'employee') {
                $hrm = Hrm::find($user_id);
                if ($hrm) {
                    $users[] = $hrm->fullname;
                }
            } else if ($send_email->user_type == 'customer') {
                $customer = Customer::find($user_id);
                if ($customer) {
                    $users[] = $customer->company ?: $customer->name;
                }
            } else if ($send_email->user_type == 'supplier') {
                $supplier = Supplier::find($user_id);
                if ($supplier) {
                    $users[] = $supplier->company ?: $supplier->name;
                }
            } else if ($send_email->user_type == 'labourer') {
                $labourer = CasualLabourer::find($user_id);
                if ($labourer) {
                    $users[] = $labourer->name;
                }
            }
        }

        $user_names = implode(', ', $users);
        //returning with successfull message
        return new ViewResponse('focus.send_emails.view', compact('send_email', 'user_names'));
    }
}
