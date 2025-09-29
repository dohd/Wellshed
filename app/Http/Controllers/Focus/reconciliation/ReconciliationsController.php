<?php

namespace App\Http\Controllers\Focus\reconciliation;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\banktransfer\Banktransfer;
use App\Models\billpayment\Billpayment;
use App\Models\charge\Charge;
use App\Models\creditnote\CreditNote;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\JournalItem;
use App\Models\reconciliation\Reconciliation;
use App\Models\reconciliation\ReconciliationItem;
use App\Repositories\Focus\reconciliation\ReconciliationRepository;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ReconciliationsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ReconciliationRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ReconciliationRepository $repository ;
     */
    public function __construct()
    {
        $this->repository = new ReconciliationRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $accounts = Account::whereHas('reconciliations')->get(['id', 'number', 'holder']);
        return new ViewResponse('focus.reconciliations.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = Reconciliation::max('tid')+1;
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system_rel', ['bank', 'cash']))
            ->get(['id', 'holder']);
        $last_day = date('Y-m-t', strtotime(date('Y-m-d')));

        return new ViewResponse('focus.reconciliations.create', compact('tid', 'accounts', 'last_day'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'end_date' => 'required',
            'ending_period' => 'required',
            'reconciled_on' => 'required',
            'end_balance' => 'required',
        ]);

        if ($request->end_date != substr($request->ending_period, 3))
            throw ValidationException::withMessages(['Ending Period should be of same Ending Month']);
            
        try {
            $model = $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Reconciliation', $th);
        }
        
        return new RedirectResponse(route('biller.reconciliations.show', $model), ['flash_success' => 'Reconcilliaton Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Reconciliation $reconciliation
     * @return \Illuminate\Http\Response
     */
    public function edit(Reconciliation $reconciliation)
    {
        $tid = $reconciliation->tid;
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system_rel', ['bank', 'cash']))
            ->get(['id', 'holder']);
        // set beginning balance
        if (!round($reconciliation->begin_balance,2)) {
            $accountId = $reconciliation->account_id;
            $monthYear = explode('-', $reconciliation->end_date);
            $reconciliation->begin_balance = $this->beginningBalance($accountId, $monthYear[0], $monthYear[1]);
        }

        // dynamically set receipt amount as the amount
        foreach ($reconciliation->items as $key => $item) {
            $bank_transfer = $item->bank_transfer;
            if ($bank_transfer && $bank_transfer->dest_account->id == $reconciliation->account_id) {
                $xfer_amount = +$bank_transfer->receipt_amount;
                if ($xfer_amount == 0) {
                    $srcCurrency = @$bank_transfer->source_account->currency;
                    $destCurrency = @$bank_transfer->dest_account->currency;
                    if ($srcCurrency->rate == 1 && $destCurrency->rate == 1) {
                        $xfer_amount = $bank_transfer->amount;
                    } elseif (+$bank_transfer->default_rate) {
                        if (round($bank_transfer->bank_rate) < round($bank_transfer->default_rate)) {
                            $xfer_amount = round($bank_transfer->amount / $bank_transfer->default_rate,4);
                        } else {
                            $xfer_amount = round($bank_transfer->amount * $bank_transfer->default_rate,4);
                        }
                    } else {
                        $xfer_amount = 0;
                    }
                }
                $bank_transfer->amount = $xfer_amount;
                $reconciliation['items'][$key]['bank_transfer'] = $bank_transfer;
            }
        }
        
        return new ViewResponse('focus.reconciliations.edit', compact('tid', 'reconciliation', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Reconciliation $reconciliation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reconciliation $reconciliation)
    {
        $request->validate([
            'ending_period' => 'required',
            'reconciled_on' => 'required',
            'end_balance' => 'required',
        ]);

        if ($reconciliation->end_date != substr($request->ending_period, 3))
            throw ValidationException::withMessages(['Ending Period should be of same Ending Month']);
        
        try {
            $this->repository->update($reconciliation, $request->except('_token', '_method'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Reconciliation', $th);
        }
        
        return new RedirectResponse(route('biller.reconciliations.show', $reconciliation), ['flash_success' => 'Reconcilliaton Updated Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Reconciliation $reconciliation)
    {
        // dynamically set receipt amount as the amount
        foreach ($reconciliation->items as $key => $item) {
            $bank_transfer = $item->bank_transfer;
            if ($bank_transfer && $bank_transfer->dest_account->id == $reconciliation->account_id) {
                $xfer_amount = +$bank_transfer->receipt_amount;
                if ($xfer_amount == 0) {
                    $srcCurrency = @$bank_transfer->source_account->currency;
                    $destCurrency = @$bank_transfer->dest_account->currency;
                    if ($srcCurrency->rate == 1 && $destCurrency->rate == 1) {
                        $xfer_amount = $bank_transfer->amount;
                    } elseif (+$bank_transfer->default_rate) {
                        if (round($bank_transfer->bank_rate) < round($bank_transfer->default_rate)) {
                            $xfer_amount = round($bank_transfer->amount / $bank_transfer->default_rate,4);
                        } else {
                            $xfer_amount = round($bank_transfer->amount * $bank_transfer->default_rate,4);
                        }
                    } else {
                        $xfer_amount = 0;
                    }
                }
                $bank_transfer->amount = $xfer_amount;
                $reconciliation['items'][$key]['bank_transfer'] = $bank_transfer;
            }
        }        
        
        return new ViewResponse('focus.reconciliations.view', compact('reconciliation'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reconciliation $reconciliation)
    {
        $this->repository->delete($reconciliation);

        return new RedirectResponse(route('biller.reconciliations.index'), ['flash_sucess' => 'Reconciliation deleted successfully']);
    }

    /**
     * Reconciliation transaction items
     */
    public function accountItems()
    {
        if (request('is_create')) {
            $reconciliationExists = Reconciliation::where('account_id', request('account_id'))
                ->where('end_date', request('end_date'))
                ->exists();
            if ($reconciliationExists) {
                return response()->json(['status' => 'Error', 'message' => 'Reconciliation Exists!'], 500);
            }
        }

        $struct = ['payment_id' => null, 'deposit_id' => null, 'client_supplier' => null, 'bank_transfer_id' => null, 'charge_id' => null, 'journal_item_id' => null, 'man_journal_id' => null];
        $date = explode('-', request('end_date'));
        $account_items = collect();

        // journal items
        $journal_items = JournalItem::when(request('journal_item_ids'), function($q) {
            $q->whereNotIn('journal_items.id', explode(',', request('journal_item_ids')));
        })
        ->where('account_id', request('account_id'))
        ->whereHas('journal', function ($q) use($date) {
            $q->whereNull('customer_id')->whereNull('supplier_id')->whereNull('account_id');
            $q->where(function($q) use($date) {
                if (request('is_after_end_date')) {
                    $q->whereMonth('date', '>', $date[0]);
                    $q->whereYear('date', '>=', $date[1]);
                } else {
                    $q->whereMonth('date', $date[0]);
                    $q->whereYear('date', $date[1]);
                }
            });
        })
        ->get()
        ->each(function($item) use($account_items, $struct) {
            $acc_item = array_replace($struct, [
                'journal_item_id' => $item->id,
                'man_journal_id' => $item->journal_id,
                'date' => @$item->journal->date,
                'type' => $item->debit == 0? 'cash-out' : 'cash-in',
                'trans_ref' => gen4tid('JNL-', @$item->journal->tid),
                'note' => @$item->journal->note,
                'amount' => $item->debit == 0? $item->credit : $item->debit,
            ]);
            $account_items->add($acc_item); 
        });

        // bank transfers
        $bank_transfers = Banktransfer::when(request('bank_transfer_ids'), function($q) {
            $q->whereNotIn('bank_transfers.id', explode(',', request('bank_transfer_ids')));
        })
        ->where(function ($q) {
            $q->where('account_id', request('account_id'))
            ->orWhere('debit_account_id', request('account_id'));
        })
        ->where(function($q) use($date) {
            if (request('is_after_end_date')) {
                $q->whereMonth('transaction_date', '>', $date[0]);
                $q->whereYear('transaction_date', '>=', $date[1]);
            } else {
                $q->whereMonth('transaction_date', $date[0]);
                $q->whereYear('transaction_date', $date[1]);
            }
        })
        ->get()
        ->each(function($item) use($account_items, $struct) {
            // convert recepient account currency
            if (request('account_id') == @$item->dest_account->id) {
                $receiptAmount = +$item->receipt_amount;
                if ($receiptAmount == 0) {
                    $srcCurrency = @$item->source_account->currency;
                    $destCurrency = @$item->dest_account->currency;
                    if ($srcCurrency->rate == 1 && $destCurrency->rate == 1) {
                        $receiptAmount = $item->amount;
                    } elseif (+$item->default_rate) {
                        if (round($item->bank_rate) < round($item->default_rate)) {
                            $receiptAmount = round($item->amount / $item->default_rate,4);
                        } else {
                            $receiptAmount = round($item->amount * $item->default_rate,4);
                        }
                    } else {
                        $receiptAmount = 0;
                    }
                }
                $item->amount = $receiptAmount;
            }
            $acc_item = array_replace($struct, [
                'bank_transfer_id' => $item->id,
                'date' => $item->transaction_date,
                'type' => $item->account_id == request('account_id')? 'cash-out' : 'cash-in',
                'trans_ref' => gen4tid('XFER-', $item->tid),
                'note' => $item->note? "{$item->method} - {$item->refer_no} - {$item->note}" : "{$item->method} - {$item->refer_no}",
                'amount' => $item->amount,
            ]);
            $account_items->add($acc_item); 
        });

        // account charges
        $account_charges = Charge::when(request('charge_ids'), function($q) {
            $q->whereNotIn('charges.id', explode(',', request('charge_ids')));
        })
        ->where('bank_id', request('account_id'))
        ->where(function($q) use($date) {
            if (request('is_after_end_date')) {
                $q->whereMonth('date', '>', $date[0]);
                $q->whereYear('date', '>=', $date[1]);
            } else {
                $q->whereMonth('date', $date[0]);
                $q->whereYear('date', $date[1]);
            }
        })
        ->get()
        ->each(function($item) use($account_items, $struct) {
            $acc_item = array_replace($struct, [
                'charge_id' => $item->id,
                'date' => $item->date,
                'type' => 'cash-out',
                'trans_ref' => gen4tid('CHRG-', $item->tid),
                'note' => $item->note? "{$item->payment_mode} - {$item->reference} - {$item->note}" : "{$item->payment_mode} - {$item->reference}",
                'amount' => $item->amount,
            ]);
            $account_items->add($acc_item); 
        });

        // payments
        $payments = Billpayment::when(request('payment_ids'), function($q) {
            $q->whereNotIn('bill_payments.id', explode(',', request('payment_ids')));
        })
        ->whereHas('supplier')
        ->whereNull('rel_payment_id')
        ->where('account_id', request('account_id'))
        ->where(function($q) use($date) {
            if (request('is_after_end_date')) {
                $q->whereMonth('date', '>', $date[0]);
                $q->whereYear('date', '>=', $date[1]);
            } else {
                $q->whereMonth('date', $date[0]);
                $q->whereYear('date', $date[1]);
            }
        })
        ->when(request('is_after'), function($q) use($date) {
            $q->whereMonth('date', '>', $date[0])->whereYear('date', '>=', $date[1]);
        })
        ->get()
        ->each(function($item) use($account_items, $struct) {
            $acc_item = array_replace($struct, [
                'payment_id' => $item->id,
                'date' => $item->date,
                'type' => 'cash-out',
                'trans_ref' => gen4tid('RMT-', $item->tid),
                'client_supplier' => @$item->supplier->name,
                'note' => $item->note,
                'amount' => $item->amount,
            ]);
            $account_items->add($acc_item); 
        });
        // dd(request('payment_ids'), $payments->pluck('id')->toArray(), $payments->toArray());

        // deposits and receipts
        $deposits = InvoicePayment::when(request('deposit_ids'), function($q) {
            $q->whereNotIn('paid_invoices.id', explode(',', request('deposit_ids')));
        })
        ->whereHas('customer')
        ->whereNull('rel_payment_id')
        ->where('account_id', request('account_id'))
        ->where(function($q) use($date) {
            if (request('is_after_end_date')) {
                $q->whereMonth('date', '>', $date[0]);
                $q->whereYear('date', '>=', $date[1]);
            } else {
                $q->whereMonth('date', $date[0]);
                $q->whereYear('date', $date[1]);
            }
        })
        ->get()
        ->each(function($item) use($account_items, $struct) {
            $acc_item = array_replace($struct, [
                'deposit_id' => $item->id,
                'date' => $item->date,
                'type' => 'cash-in',
                'trans_ref' => gen4tid('PMT-', $item->tid),
                'client_supplier' => @$item->customer->company ?: @$item->customer->name,
                'note' => $item->note,
                'amount' => $item->amount,
            ]);
            $account_items->add($acc_item); 
        });
        // dd(request('deposit_ids'), $deposits->pluck('id')->toArray(), $deposits->toArray(), $date);

        // credit notes and refunds
        $creditnotes = CreditNote::when(request('creditnote_ids'), function($q) {
            $q->whereNotIn('credit_notes.id', explode(',', request('creditnote_ids')));
        })
        ->whereHas('customer')
        ->where('account_id', request('account_id'))
        ->where(function($q) use($date) {
            if (request('is_after_end_date')) {
                $q->whereMonth('date', '>', $date[0]);
                $q->whereYear('date', '>=', $date[1]);
            } else {
                $q->whereMonth('date', $date[0]);
                $q->whereYear('date', $date[1]);
            }
        })
        ->get()
        ->each(function($item) use($account_items, $struct) {
            $acc_item = array_replace($struct, [
                'creditnote_id' => $item->id,
                'date' => $item->date,
                'type' => 'cash-out',
                'trans_ref' => gen4tid('CN-', $item->tid),
                'client_supplier' => @$item->customer->company ?: @$item->customer->name,
                'note' => $item->note,
                'amount' => $item->total,
            ]);
            $account_items->add($acc_item); 
        });

        // sort by date and append beginning balance
        $sorted_items = $account_items->sortBy('date');
        $account_items = collect([...$sorted_items])->map(function($v) use($date) {
            $v['begin_balance'] = $this->beginningBalance(request('account_id'), $date[0], $date[1]);
            return $v;
        });
        // decouple beginning balance from account_items collection
        
        return response()->json($account_items);
    }

    /**
     * Previous Reconciliation Uncleared Account Items
     */
    public function prevUnclearedAccountItems() 
    {
        $accountId = request('account_id');
        $endDate = request('end_date');

        $accountItems = [];
        $prevMonth = Carbon::createFromFormat('m-Y', $endDate)->subMonth()->format('m-Y');
        $prevReconciliation = Reconciliation::where('account_id', $accountId)
            ->where('end_date', $prevMonth)
            ->first();
        if ($prevReconciliation) {
            $clearedItems = $prevReconciliation->items()->where('checked',1)->get();
            $journal_item_ids = $clearedItems->whereNotNull('journal_item_id')->pluck('journal_item_id')->implode(',');
            $bank_transfer_ids = $clearedItems->whereNotNull('bank_transfer_id')->pluck('bank_transfer_id')->implode(',');
            $charge_ids = $clearedItems->whereNotNull('charge_id')->pluck('charge_id')->implode(',');
            $payment_ids = $clearedItems->whereNotNull('payment_id')->pluck('payment_id')->implode(',');
            $deposit_ids = $clearedItems->whereNotNull('deposit_id')->pluck('deposit_id')->implode(',');
            $creditnote_ids = $clearedItems->whereNotNull('creditnote_id')->pluck('creditnote_id')->implode(',');
            $params = compact('journal_item_ids', 'bank_transfer_ids', 'charge_ids', 'payment_ids', 'deposit_ids', 'creditnote_ids');
            $params = array_merge($params, ['account_id' => $accountId, 'end_date' => $prevMonth]);
            // overwrite request params
            request()->merge($params);
            $accountItems = $this->accountItems()->original;
        }
       
        return response()->json($accountItems);
    }


    /**
     * Uncleared account items after end month
     */
    public function postUnclearedAccountItems() 
    {
        $accountId = request('account_id');
        $endDate = request('end_date');

        $nextMonth = Carbon::createFromFormat('m-Y', $endDate)->addMonth()->format('m-Y');

        $modelItems = ReconciliationItem::where('checked', 1)
        ->whereHas('reconciliation', fn($q) => $q->where('account_id', $accountId))
        ->get();

        $journal_item_ids = $modelItems->pluck('journal_item_id')->filter()->implode(',');
        $bank_transfer_ids = $modelItems->pluck('bank_transfer_id')->filter()->implode(',');
        $charge_ids = $modelItems->pluck('charge_id')->filter()->implode(',');
        $payment_ids = $modelItems->pluck('payment_id')->filter()->implode(',');
        $deposit_ids = $modelItems->pluck('deposit_id')->filter()->implode(',');
        $creditnote_ids = $modelItems->pluck('creditnote_id')->filter()->implode(',');
        $params = compact('journal_item_ids', 'bank_transfer_ids', 'charge_ids', 'payment_ids', 'deposit_ids', 'creditnote_ids');
        $params = array_merge($params, ['account_id' => $accountId, 'end_date' => $nextMonth]);
        // overwrite request params
        request()->merge($params);
        $accountItems = $this->accountItems()->original;

        return response()->json($accountItems);
    }    

    /**
     * Account Beginning Balance
     */
    public function beginningBalance($account_id, $month='', $year='') 
    {
        $beginningBalance = 0;

        // set account opening balance
        $account = Account::find($account_id);
        if ($account->opening_balance_date) {
            $ob_date = explode('-', $account->opening_balance_date);
            if ($ob_date[0] == $year && $ob_date[1] == $month) {
                $beginningBalance = $account->opening_balance;
            }
        }

        // set previous reconciliation ending balance
        $prevMonth = Carbon::createFromFormat('m-Y', "{$month}-{$year}")->subMonth()->format('m-Y');
        $lastRecon =  Reconciliation::where('account_id', $account_id)
            ->where('end_date', 'LIKE', "%{$prevMonth}%")
            ->where('balance_diff', 0)
            ->latest()
            ->first();
        if ($lastRecon) {
            $beginningBalance = $lastRecon->end_balance;
        }

        return $beginningBalance;
    }

    /**
     * Reconciliation Account Debit Balance
     */
    public function accountBalance(Request $request) 
    {
        $endingPeriodBal = 0;
        $reconciledOnBal = 0;
        $reconciled_on = date_for_database($request->reconciled_on);
        $ending_period = date_for_database($request->ending_period);
        $starting_period = Carbon::parse($ending_period)->firstOfMonth()->format('Y-m-d');

        $account = Account::find($request->account_id);
        if (@$account->currency->rate == 1) {
            $endingPeriodBal = $account->transactions()
                ->whereBetween('tr_date', [$starting_period, $ending_period])
                ->sum(\DB::raw('debit - credit'));
            $reconciledOnBal = $account->transactions()
                ->whereBetween('tr_date', [$starting_period, $reconciled_on])
                ->sum(\DB::raw('debit - credit'));
        } else {
            $endingPeriodBal = $account->transactions()
                ->whereBetween('tr_date', [$starting_period, $ending_period])
                ->sum(\DB::raw('fx_debit - fx_credit'));
            $reconciledOnBal = $account->transactions()
                ->whereBetween('tr_date', [$starting_period, $reconciled_on])
                ->sum(\DB::raw('fx_debit - fx_credit'));
        }

        return response()->json([
            'starting_period_date' => $starting_period,
            'ending_period_date' => $ending_period,
            'reconciled_on_date' => $reconciled_on,
            'ending_period' => $endingPeriodBal,
            'reconciled_on' => $reconciledOnBal,
        ]);
    }

    /**
     * Reconciliation Report PDF Export
     */
    public function printPDF(Reconciliation $reconciliation)
    {
        $modelItems = collect();
        $reconciliationItems = $reconciliation->items()
            ->with('journal')
            ->with('journal_item.supplier')
            ->with('deposit.customer')
            ->with('payment.supplier')
            ->with('creditnote.customer')
            ->with('bank_transfer')
            ->with('charge')
            ->get();
        foreach ($reconciliationItems as $item) {
            $item1 = [];
            if ($item->journal && $item->journal_item) {
                $item1 = [
                    'checked' => $item->checked,
                    'date' => dateFormat($item->journal->date),
                    'source' => 'Journal',
                    'type' => $item->journal_item->debit > 0? 'cash-in' : 'cash-out',
                    'ref_no' => gen4tid('', $item->journal->tid),
                    'payee' => @$item->journal_item->supplier->name,
                    'note' => $item->journal->note,
                    'debit' => $item->journal_item->debit,
                    'credit' => $item->journal_item->credit,
                ];
            } elseif ($item->deposit) {
                $item1 = [
                    'checked' => $item->checked,
                    'date' => dateFormat($item->deposit->date),
                    'source' => 'Receive Payment',
                    'type' => 'cash-in',
                    'ref_no' => gen4tid('', $item->deposit->tid),
                    'payee' => @$item->deposit->customer->company ?: @$item->deposit->customer->name,
                    'note' => $item->deposit->note,
                    'debit' => $item->deposit->amount,
                    'credit' => 0,
                ];
            } elseif ($item->payment) {
                $item1 = [
                    'checked' => $item->checked,
                    'date' => dateFormat($item->payment->date),
                    'source' => 'Bill Payment',
                    'type' => 'cash-out',
                    'ref_no' => gen4tid('', $item->payment->tid),
                    'payee' => @$item->payment->supplier->name,
                    'note' => $item->payment->note,
                    'debit' => 0,
                    'credit' => $item->payment->amount,
                ];
            } elseif ($item->creditnote) {
                $item1 = [
                    'checked' => $item->checked,
                    'date' => dateFormat($item->creditnote->date),
                    'source' => 'Credit Note',
                    'type' => 'cash-out',
                    'ref_no' => gen4tid('', $item->creditnote->tid),
                    'payee' => @$item->creditnote->customer->company ?: @$item->creditnote->customer->name,
                    'note' => $item->creditnote->note,
                    'debit' => 0,
                    'credit' => $item->creditnote->amount,
                ];
            } elseif ($item->charge) {
                $item1 = [
                    'checked' => $item->checked,
                    'date' => dateFormat($item->charge->date),
                    'source' => 'Charge',
                    'type' => 'cash-out',
                    'ref_no' => gen4tid('', $item->charge->tid),
                    'payee' => '',
                    'note' => $item->charge->note,
                    'debit' => 0,
                    'credit' => $item->charge->amount,
                ];
            } elseif ($item->bank_transfer) {
                $type = $reconciliation->account_id == $item->bank_transfer->account_id? 'cash-out' : 'cash-in';
                $item1 = [
                    'checked' => $item->checked,
                    'date' => dateFormat($item->bank_transfer->transaction_date),
                    'source' => 'Transfer',
                    'type' => $type,
                    'ref_no' => gen4tid('', $item->bank_transfer->tid),
                    'payee' => '',
                    'note' => $item->bank_transfer->note,
                    'debit' => $type == 'cash-in'? $item->bank_transfer->amount : 0,
                    'credit' => $type == 'cash-out'? $item->bank_transfer->amount : 0,
                ];
            }
            if (!$item1) continue; 
            $modelItems->add(ReconciliationItem::make($item1));
        }

        // mPDF logic
        $model = $reconciliation;
        $model->items = $modelItems;
        $company = auth()->user()->business;
        $html = view('focus.reconciliations.print_reconciliation_detail', compact('model', 'company'))->render();
        if (request('type') == 'summary') {
            $html = view('focus.reconciliations.print_reconciliation_summary', compact('model', 'company'))->render();
        }
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        $headers = array(
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        return response()->stream($pdf->Output('Reconciliation-Report.pdf', 'I'), 200, $headers);
    }
}
