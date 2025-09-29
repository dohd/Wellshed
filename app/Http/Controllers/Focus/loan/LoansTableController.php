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
namespace App\Http\Controllers\Focus\loan;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\loan\LoanRepository;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class BanksTableController.
 */
class LoansTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var LoanRepository
     */
    protected $loan;

    /**
     * contructor to initialize repository object
     * @param LoanRepository $loan ;
     */
    public function __construct(LoanRepository $loan)
    {
        $this->loan = $loan;
    }

    /**
     * This method return the data of the model
     *
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->loan->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('lender', function ($loan) {
                if ($loan->lender) return $loan->lender->name;
                return 'N/A';
            })
            ->addColumn('borrower', function ($loan) {
                if ($loan->employee) return $loan->employee->full_name;
                return 'N/A';
            })
            ->addColumn('date', function ($loan) {
                return dateFormat($loan->date);
            })
            ->addColumn('amount', function ($loan) {
                return NumberFormat($loan->amount + $loan->application_fee);
            })
            ->addColumn('amountpaid', function ($loan) {
                return numberFormat($loan->amountpaid);
            })
            ->addColumn('installment', function ($loan) {
                return numberFormat($loan->month_installment);
            })
            ->addColumn('interest', function ($loan) {
                return numberFormat($loan->interest);
            })
            ->addColumn('status', function ($loan) {
                return $loan->approval_status;
            })
            ->addColumn('actions', function ($loan) {
                return $loan->action_buttons;
            })
            ->make(true);
    }
}