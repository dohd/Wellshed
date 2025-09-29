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

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\banktransfer\BanktransferRepository;
use App\Http\Requests\Focus\banktransfer\ManageBanktransferRequest;
use App\Models\account\Account;

/**
 * Class BanksTableController.
 */
class BanktransfersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var BankRepository
     */
    protected $banktransfer;

    /**
     * contructor to initialize repository object
     * @param BankRepository $banktransfer ;
     */
    public function __construct(BanktransferRepository $banktransfer)
    {
        $this->banktransfer = $banktransfer;
    }

    /**
     * This method return the data of the model
     * @param ManageBankRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageBanktransferRequest $request)
    {
        $core = $this->banktransfer->getForDataTable();
        $account = Account::where('system', 'pool_petty_cash')
            ->whereHas('currency')
            ->with(['currency' => fn($q) => $q->select('id', 'code', 'rate')->get()])
            ->with(['transactions' => fn($q) => $q->selectRaw('account_id, SUM(debit) debit, SUM(credit) credit, SUM(fx_debit) fx_debit, SUM(fx_credit) fx_credit')->groupBy('account_id')])
            ->first();

        $sum_of_returned_cash = 0;
        $sum_of_received_cash = 0;
        foreach ($core->get() as $transfer) {
            if(!$account) continue; 
            //Transfer from petty cash account to eg bank (Returned Cash)
            if($transfer->account_id == $account->id)
            {
                $sum_of_returned_cash += $transfer->amount;
            }
            //Transfer to Petty Cash account (received cash)
            elseif($transfer->debit_account_id == $account->id){
                $sum_of_received_cash += $transfer->amount;
            }
        }
        //balance
        $amount = $sum_of_received_cash - $sum_of_returned_cash;
        $transfer_amt = numberClean($amount);
        $amount = amountFormat($amount);
        $aggregate = compact('amount','transfer_amt');
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('tid', function ($banktransfer) {
                return gen4tid('XFER-', $banktransfer->tid);
            })
            ->addColumn('source_account', function ($banktransfer) {
                return @$banktransfer->source_account->holder;
            })
            ->addColumn('recepient_account', function ($banktransfer) {
                return @$banktransfer->dest_account->holder;
            })
            ->addColumn('mode', function ($banktransfer) {
                $mode = $banktransfer->method;
                if ($banktransfer->refer_no) $mode = "{$mode} - {$banktransfer->refer_no}"; 
                return $mode;
            })
            ->filterColumn('account', function($query, $account) {
                $query->whereHas('source_account', fn($q) => $q->where('holder', 'LIKE', "%{$account}%"));
            })
            ->addColumn('credit', function ($banktransfer) {
                $currencyId = @$banktransfer->source_account->currency_id;
                return amountFormat($banktransfer->amount, $currencyId);
            })
            ->addColumn('debit', function ($banktransfer) {
                $currency = @$banktransfer->dest_account->currency;
                $receiptAmount = +$banktransfer->receipt_amount;
                if (!$receiptAmount) {
                    if ($currency) {
                        if ($banktransfer->bank_rate == 1) $receiptAmount = $banktransfer->amount;
                        elseif ($banktransfer->bank_rate) $receiptAmount = round($banktransfer->amount * $banktransfer->bank_rate, 4);
                    }
                }
                return amountFormat($receiptAmount, @$currency->id);
            })
            ->addColumn('transaction_date', function ($banktransfer) {
                return dateFormat($banktransfer->transaction_date);
            })
            ->orderColumn('transaction_date', '-transaction_date $1')
            ->addColumn('aggregate', function () use($aggregate) {
                return $aggregate;
            })
            ->addColumn('actions', function ($banktransfer) {
                return $banktransfer->action_buttons;
            })
            ->make(true);
    }
}
