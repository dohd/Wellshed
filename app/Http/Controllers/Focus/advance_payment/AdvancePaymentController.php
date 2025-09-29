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

namespace App\Http\Controllers\Focus\advance_payment;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\User\User;
use App\Models\advance_payment\AdvancePayment;
use App\Models\hrm\Hrm;
use App\Repositories\Focus\advance_payment\AdvancePaymentRepository;
use DateTime;
use Illuminate\Http\Request;

class AdvancePaymentController extends Controller
{
    /**
     * variable to store the repository object
     * @var AdvancePaymentRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AdvancePaymentRepository $repository ;
     */
    public function __construct(AdvancePaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.advance_payments.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $dateToday = (new DateTime())->format('j');
        if (intval($dateToday) < 15) return new RedirectResponse(route('biller.advance_payments.index'), ['flash_error' => 'Advance Payment Application Opens On The 15th of Every Month']);

        $users = Hrm::with('employees_salary')
            ->get()
            ->map(function ($user) {

                return (object) [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'limit' =>  floatval(optional($user->employees_salary)->basic_salary * 0.7)
                ];
            });

        return view('focus.advance_payments.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->repository->create($request->except('_token'));

        return new RedirectResponse(route('biller.advance_payments.index'), ['flash_success' => 'Advance Payment Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  AdvancePayment $advance_payment
     * @return \Illuminate\Http\Response
     */
    public function edit(AdvancePayment $advance_payment)
    {
        $users = Hrm::with('employees_salary')
            ->get()
            ->map(function ($user) {

                return (object) [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'limit' =>  floatval(optional($user->employees_salary)->basic_salary * 0.7)
                ];
            });

        return view('focus.advance_payments.edit', compact('advance_payment', 'users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  AdvancePayment $advance_payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AdvancePayment $advance_payment)
    {
        $this->repository->update($advance_payment, $request->except('_token'));

        return new RedirectResponse(route('biller.advance_payments.index'), ['flash_success' => 'Advance Payment Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  AdvancePayment $advance_payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(AdvancePayment $advance_payment)
    {
        $this->repository->delete($advance_payment);

        return new RedirectResponse(route('biller.advance_payments.index'), ['flash_success' => 'Advance Payment Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  AdvancePayment $advance_payment
     * @return \Illuminate\Http\Response
     */
    public function show(AdvancePayment $advance_payment)
    {
        return view('focus.advance_payments.view', compact('advance_payment'));
    }
}
