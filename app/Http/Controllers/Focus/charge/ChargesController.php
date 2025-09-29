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

namespace App\Http\Controllers\Focus\charge;

use App\Models\charge\Charge;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\charge\CreateResponse;
use App\Http\Responses\Focus\charge\EditResponse;
use App\Repositories\Focus\charge\ChargeRepository;
use App\Http\Requests\Focus\charge\ManageChargeRequest;
use App\Http\Requests\Focus\charge\StoreChargeRequest;


/**
 * BanksController
 */
class ChargesController extends Controller
{
    /**
     * variable to store the repository object
     * @var ChargeRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ChargeRepository $repository ;
     */
    public function __construct(ChargeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\bank\ManageBankRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageChargeRequest $request)
    {
        $words = array();
        return new ViewResponse('focus.charges.index', compact('words'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\CreateResponse
     */
    public function create(StoreChargeRequest $request)
    {
        return new CreateResponse('focus.charges.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreBankRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreChargeRequest $request)
    {
        $data = $request->only([
            'tid', 'bank_id', 'expense_id', 'amount', 'payment_mode', 'date',
            'reference', 'note'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        try {
            $this->repository->create($data);
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Charge!', $th);
        }

        return new RedirectResponse(route('biller.charges.index'), ['flash_success' => 'Charge successfully created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\bank\Bank $bank
     * @param EditBankRequestNamespace $request
     * @return \App\Http\Responses\Focus\bank\EditResponse
     */
    public function edit(Charge $charge, StoreChargeRequest $request)
    {
        return new EditResponse($charge);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreChargeRequest $request, Charge $charge)
    {
        $request->validate([
            'name' => 'required|string',
            'bank' => 'required|string',
            'number' => 'required'
        ]);

        if (@$charge->reconciliation_item->checked)
            throw ValidationException::withMessages(['Not Allowed! Charge has been reconciled']);

        try {
            $this->repository->update($charge, $request->except(['_token', 'ins']));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Charge!', $th);
        }

        return new RedirectResponse(route('biller.charges.index'), ['flash_success' => 'Charge successfully updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Charge $charge, StoreChargeRequest  $request)
    {
        if (@$charge->reconciliation_item->checked)
            throw ValidationException::withMessages(['Not Allowed! Charge has been reconciled']);

        try {
            $this->repository->delete($charge);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Charge!', $th);
        }

        return new RedirectResponse(route('biller.charges.index'), ['flash_success' => 'Charge successfully deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBankRequestNamespace $request
     * @param App\Models\bank\Bank $bank
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Charge $charge, ManageChargeRequest $request)
    {
        return new ViewResponse('focus.charges.view', compact('charge'));
    }
}
