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

namespace App\Http\Controllers\Focus\banktransfer;

use App\Models\banktransfer\Banktransfer;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\banktransfer\CreateResponse;
use App\Http\Responses\Focus\banktransfer\EditResponse;
use App\Repositories\Focus\banktransfer\BanktransferRepository;
use App\Http\Requests\Focus\banktransfer\ManageBanktransferRequest;
use App\Http\Requests\Focus\banktransfer\StoreBanktransferRequest;
use Illuminate\Validation\ValidationException;

/**
 * BanksController
 */
class BanktransfersController extends Controller
{
    /**
     * variable to store the repository object
     * @var BankRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param BankRepository $repository ;
     */
    public function __construct(BanktransferRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\bank\ManageBankRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageBanktransferRequest $request)
    {
        $words = array();
        return new ViewResponse('focus.banktransfers.index', compact('words'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\CreateResponse
     */
    public function create()
    {
        return new CreateResponse('focus.banktransfers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreBankRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreBanktransferRequest $request)
    {
        $request->validate([
            'bank_rate' => 'required',
            'default_rate' => 'required',
            'amount' => 'required',
            'receipt_amount' => 'required',
            'account_id' => 'required',
            'debit_account_id' => 'required|different:account_id',
        ]);
        
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) { 
            return errorHandler('Error Creating Money Transfer!', $th);
        }

        return new RedirectResponse(route('biller.banktransfers.index'), ['flash_success' => 'Money Transfer Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\bank\Bank $bank
     * @param EditBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\EditResponse
     */
    public function edit(Banktransfer $banktransfer)
    {
        return new EditResponse($banktransfer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreBanktransferRequest $request, Banktransfer $banktransfer)
    {
        $request->validate([
            'bank_rate' => 'required',
            'default_rate' => 'required',
            'amount' => 'required',
            'receipt_amount' => 'required',
            'account_id' => 'required',
            'debit_account_id' => 'required|different:account_id',
        ]);

        if (@$banktransfer->reconciliation_item->checked)
            throw ValidationException::withMessages(['Not Allowed! Transfer has been reconciled']);
        
        try {
            $this->repository->update($banktransfer, $request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Money Transfer!', $th);
        }

        return new RedirectResponse(route('biller.banktransfers.index'), ['flash_success' => 'Money Tranfer Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Banktransfer $banktransfer)
    {
        if (@$banktransfer->reconciliation_item->checked)
            throw ValidationException::withMessages(['Not Allowed! Transfer has been reconciled']);

        try {
            $this->repository->delete($banktransfer);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Money Transfer!', $th);
        }

        return new RedirectResponse(route('biller.banktransfers.index'), ['flash_success' => 'Money Tranfer Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Banktransfer $banktransfer)
    {   
        return new ViewResponse('focus.banktransfers.view', compact('banktransfer'));
    }
}
