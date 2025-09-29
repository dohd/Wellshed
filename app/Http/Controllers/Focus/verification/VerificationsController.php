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

namespace App\Http\Controllers\Focus\verification;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\additional\Additional;
use App\Models\customer\Customer;
use App\Models\lpo\Lpo;
use App\Models\project\Project;
use App\Models\quote\Quote;
use App\Models\verification\Verification;
use App\Repositories\Focus\verification\VerificationRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VerificationsController extends Controller
{
    /**
     * variable to store the repository object
     * @var VerificationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param VerificationRepository $repository ;
     */
    public function __construct(VerificationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customers = Customer::whereHas('quotes', fn($q) => $q->where(['verified' => 'Yes', 'invoiced' => 'No']))
            ->get(['id', 'company']);
        $lpos = Lpo::whereHas('quotes', fn($q) =>  $q->where(['verified' => 'Yes', 'invoiced' => 'No']))
            ->get(['id', 'lpo_no', 'customer_id']);
        $projects = Project::whereHas('quote', fn($q) => $q->where(['verified' => 'Yes', 'invoiced' => 'No']))
            ->get(['id', 'name', 'customer_id']);

        return view('focus.verifications.index', compact('customers', 'lpos', 'projects'));
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!$request->quote_id) return redirect(route('biller.verifications.quote_index'));

        $quote = Quote::findOrFail($request->quote_id);
        if ($quote->tax_id > 0) {
            $additionals = Additional::where('value', 0)->orWhere('value', $quote->tax_id)->get();
        } else {
            $additionals = Additional::where('value', 0)->get();
        }

        return view('focus.verifications.create', compact('quote', 'additionals'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {   
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            errorHandler('Error Creating Partial Verification', $th);
        }
        
        return new RedirectResponse(route('biller.verifications.index'), ['flash_success' => 'Partial Verification Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Verification $verification
     * @return \Illuminate\Http\Response
     */
    public function edit(Verification $verification)
    {
        $quote = $verification->quote ?: new Quote;
        if ($quote->tax_id > 0) $additionals = Additional::where('value', 0)->orWhere('value', $quote->tax_id)->get();
        else $additionals = Additional::where('value', 0)->get();
        $verification['jc_items'] = $verification->jc_items()->with('equipment')->get();
        
        return view('focus.verifications.edit', compact('verification', 'quote', 'additionals'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Verification $verification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Verification $verification)
    {
        try {
            $this->repository->update($verification, $request->except('_token'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            errorHandler('Error Updating Partial Verification', $th);
        }

        return new RedirectResponse(route('biller.verifications.index'), ['flash_success' => 'Partial Verification Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Verification $verification
     * @return \Illuminate\Http\Response
     */
    public function destroy(Verification $verification)
    {
        try {
            $this->repository->delete($verification);
        } catch (\Throwable $th) {
            errorHandler('Error Deleting Partial Verification', $th);
        }

        return new RedirectResponse(route('biller.verifications.index'), ['flash_success' => 'Partial Verification Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  Verification $verification
     * @return \Illuminate\Http\Response
     */
    public function show(Verification $verification)
    {
        return view('focus.verifications.view', compact('verification'));
    }

    /**
     * Display Verification Quotes Page
     * 
     */
    public function quote_index()
    {
        return view('focus.verifications.quote_index');
    }
}
