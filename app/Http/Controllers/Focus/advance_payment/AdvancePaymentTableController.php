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
use App\Repositories\Focus\advance_payment\AdvancePaymentRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class AdvancePaymentTableController extends Controller
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
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('employee', function ($advance_payment) {
                $employee = $advance_payment->employee;
                if ($employee) 
                return $employee->first_name . ' ' . $employee->last_name;
            })
            ->addColumn('date', function ($advance_payment) {
                return dateFormat($advance_payment->date);
            })
            ->addColumn('amount', function ($advance_payment) {
                return numberFormat($advance_payment->amount);
            })
            ->addColumn('approve_amount', function ($advance_payment) {
                return numberFormat($advance_payment->approve_amount);
            })
            ->addColumn('actions', function ($advance_payment) {
                return $advance_payment->action_buttons;
            })
            ->addColumn('notes', function ($advance_payment) {
                return $advance_payment->notes;
            })
            ->rawColumns(['actions', 'notes'])
            ->make(true);
    }
}
