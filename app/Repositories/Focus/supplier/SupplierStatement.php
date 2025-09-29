<?php

namespace App\Repositories\Focus\supplier;

use App\Models\billpayment\Billpayment;
use App\Models\items\JournalItem;
use App\Models\transaction\Transaction;
use App\Models\utility_bill\UtilityBill;

trait SupplierStatement
{
    public function getBillsForDataTable($supplier_id = 0)
    {
        return UtilityBill::where('supplier_id', request('supplier_id', $supplier_id))->get();
    }

    public function getTransactionsForDataTable($supplier_id = 0)
    {
        $params = ['supplier_id' => request('supplier_id', $supplier_id)];
        $q = Transaction::where('supplier_id', $params['supplier_id']);
        $q->whereHas('account', fn($q) => $q->whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['payable', 'employee_advances', 'prepaid_expenses', 'loan'])));
        $q->where(function ($q){
            $q->whereHas('bill', function($q) {
                $q->withoutGlobalScope('ins');
                // purchase order filter
                $q->when(request('purchaseorder_id'), function($q) {
                    $q->where(function($q) {
                        $q->whereHas('grn', fn($q) => $q->where('purchaseorder_id', request('purchaseorder_id')));
                        $q->orWhereHas('grn_items', function($q) {
                            $q->whereHas('goodsreceivenote', fn($q) => $q->where('purchaseorder_id', request('purchaseorder_id')));
                        });                        
                    });
                });
            });
            $q->orWhereHas('bill_payment', function($q) {
                $q->withoutGlobalScope('ins')->whereNull('rel_payment_id');
                // purchase order filter
                $q->when(request('purchaseorder_id'), function($q) {
                    $q->where(function($q) {
                        $q->whereHas('bills', function($q) {
                            $q->whereHas('grn', fn($q) => $q->where('purchaseorder_id', request('purchaseorder_id')));
                            $q->orWhereHas('grn_items', function($q) {
                                $q->whereHas('goodsreceivenote', fn($q) => $q->where('purchaseorder_id', request('purchaseorder_id')));
                            });
                        });
                    });
                });
            });

            // without purchase order filter
            if (!request('purchaseorder_id')) {
                $q->orWhereHas('manualjournal', fn($q) => $q->withoutGlobalScope('ins'));
                $q->orwhereHas('manualjournal', fn($q) => $q->withoutGlobalScope('ins')->whereHas('items'));                
            } 
        });

        // on date filter
        if (request('start_date') && request('is_transaction')) {
            $from = date_for_database(request('start_date'));
            $tr_ids = $q->pluck('id')->toArray();
            
            $params = ['id', 'tr_date', 'tr_type', 'note', 'debit', 'credit'];
            $transactions = Transaction::whereIn('id', $tr_ids)->whereBetween('tr_date', [$from, date('Y-m-d')])->get($params);

            // compute balance brought foward as of start date
            $bf_transactions = Transaction::whereIn('id', $tr_ids)->where('tr_date', '<', $from)->get($params);
            $credit_balance = $bf_transactions->sum('credit') - $bf_transactions->sum('debit');
            if ($credit_balance) {
                $record = (object) array(
                    'id' => 0,
                    'tr_date' => date('Y-m-d', strtotime($from . ' - 1 day')),
                    'tr_type' => 'balance',
                    'note' => '** Balance Brought Foward ** ',
                    'debit' => $credit_balance < 0 ? ($credit_balance * -1) : 0,
                    'credit' => $credit_balance > 0 ? $credit_balance : 0,
                );
                // merge brought foward balance with the rest of the transactions
                $transactions = collect([$record])->merge($transactions);
            }
            return $transactions;
        }

        $q->with(['bill', 'bill_payment', 'manualjournal']);
        
        return $q->get();
    }

    public function getStatementForDataTable($supplier_id = 0)
    {
        $supplierId = request('supplier_id', $supplier_id);
        $bills = UtilityBill::where('supplier_id', $supplierId)
            ->whereHas('currency')
            // purchase order filter
            ->when(request('purchaseorder_id'), function($q) {
                $q->where(function($q) {
                    $q->whereHas('grn', fn($q) => $q->where('purchaseorder_id', request('purchaseorder_id')));
                    $q->orWhereHas('grn_items', function($q) {
                        $q->whereHas('goodsreceivenote', fn($q) => $q->where('purchaseorder_id', request('purchaseorder_id')));
                    });                        
                });
            })
            ->with(['payments.bill_payment', 'currency'])
            ->orderBy('date', 'ASC')
            ->get();

        $i = 0;
        $statement = collect();
        foreach ($bills as $bill) {
            $i++;
            $bill_id = $bill->id;
            $tid = gen4tid('BILL-', $bill->tid);
            $bill_record = (object) array(
                'id' => $i,
                'date' => $bill->date,
                'type' => 'bill',
                'note' => "({$tid}) <br> {$bill->note}",
                'debit' => 0,
                'credit' => $bill->currency->rate != 1? $bill->fx_total : $bill->total,
                'bill_id' => $bill_id
            );

            $payments = collect();
            foreach ($bill->payments as $pmt) {
                if (!$pmt->bill_payment) continue;
                $i++;
                $reference = $pmt->bill_payment->reference;
                $pmt_tid = gen4tid('PMT-', $pmt->bill_payment->tid);
                $account = $pmt->bill_payment->account? $pmt->bill_payment->account->holder : '';
                $amount = numberFormat($pmt->bill_payment->amount);
                $payment_mode = ucfirst($pmt->bill_payment->payment_mode);
                $record = (object) array(
                    'id' => $i,
                    'date' => $pmt->bill->date,
                    'type' => 'payment',
                    'note' => "({$tid}) {$pmt_tid} reference: {$reference} mode: {$payment_mode} account: {$account} amount: {$amount}",
                    'debit' => $pmt->paid,
                    'credit' => 0,
                    'bill_id' => $bill_id,
                    'payment_item_id' => $pmt->id
                );
                $payments->add($record);
            }   
            $statement->add($bill_record);
            $statement = $statement->merge($payments);
        }

        return $statement;     
    }

    /**
     * Supplier Filtered Aging
     */
    public function agingFilteredBills($supplierId, $startDate)
    {
        $bills = UtilityBill::query()
            ->where('date', '<=', $startDate)
            ->where('supplier_id', $supplierId)
            ->orderBy('date', 'ASC')
            ->get(['id', 'date', 'fx_curr_rate', 'total', 'fx_total']);
            // ->sum(\DB::raw("IF(fx_curr_rate = 0 or fx_curr_rate = 1, total, fx_total)"));
        // dd(+$bills, date_for_database(request('start_date')));

        $pmts = Billpayment::query()
        ->where('date', '<=', $startDate)
        ->where('supplier_id', $supplierId)
        ->whereNull('rel_payment_id')
        ->orderBy('date', 'ASC')
        ->get(['id', 'date', 'amount']);
        // ->sum('amount');
        // dd(+$pmts, +$bills, +$bills-$pmts);
        $pmts1 = $pmts;

        // journal adjustments
        // $journalItems = JournalItem::query()
        // ->with('journal')
        // ->whereHas('journal', fn($q) => $q->where('date', '<=', $startDate))
        // ->where('supplier_id', $supplierId)
        // ->get(['id', 'credit', 'debit', 'journal_id']);
        $journalItems = collect();
        foreach ($journalItems as $item) {
            if ($item->credit > 0) {
                $bills->push((object) [
                    'id' => null,
                    'date' => $item->journal->date,
                    'total' => $item->debit,
                    'fx_curr_rate' => 1,
                    'fx_total' => 0,
                    'is_fx' => false,
                ]);
            } elseif ($item->debit > 0) {
                $pmts->push((object) [
                    'id' => null,
                    'date' => $item->journal->date,
                    'amount' => $item->credit,
                    'fx_curr_rate' => 1,
                    'fx_amount' => 0,
                    'is_fx' => false,
                ]);
            }
        }
        if ($journalItems->count()) {
            $bills = $bills->sortBy(fn($item) => $item->date); 
            $pmts = $pmts->sortBy(fn($item) => $item->date); 
        }        

        $modBills = collect();

        $pmtBucket = 0;
        $pmtQueue = $pmts;
        $currPmt = $pmtQueue->shift();

        foreach ($bills as $key => $bill) {
            $isLocal = +$bill->fx_curr_rate == 0 || +$bill->fx_curr_rate == 1; 
            $credit = round($isLocal? $bill->total : $bill->fx_total, 2);
            $debit = 0;

            // Apply payments to bills 
            while ($credit > $debit && $currPmt) {
                $pmtAmount = $currPmt->amount;
                if ($pmtBucket) $pmtAmount = 0;

                $billBal = $credit - $debit;
                $availAmount = $pmtBucket + $pmtAmount;
                if ($availAmount >= $billBal) {
                    $debit += $billBal;
                    $pmtBucket = $availAmount - $billBal;
                    if ($pmtBucket == 0) {
                        $currPmt = $pmtQueue->shift();
                    }
                    break; // Bill fully paid
                } else {
                    $debit += $availAmount;
                    $pmtBucket = 0;
                    $currPmt = $pmtQueue->shift();
                }
            }

            $modBills->add((object) [
                'id' => $bill->id,
                'date' => $bill->date,
                'debit' => $debit,
                'credit' => $credit,
            ]);
        }

        // Case of payments without bills
        if ($pmts->count() && !$bills->count()) {
            foreach ($pmts as $key => $pmt) {
                $modBills->add((object) [
                    'id' => null,
                    'date' => $pmt->date,
                    'debit' => round($pmt->amount, 2),
                    'credit' => 0,
                ]);
            }
            return $modBills;
        }

        // Handle remaining payment balance
        if ($pmtBucket && $currPmt) {
            if ($pmtBucket > 0) {
                $modBills->add((object) [
                    'id' => null,
                    'date' => $currPmt->date,
                    'debit' => $pmtBucket,
                    'credit' => 0,
                ]);
            } else {
                $modBills->add((object) [
                    'id' => null,
                    'date' => $currPmt->date,
                    'debit' => 0,
                    'credit' => $pmtBucket,
                ]);
            }
        } 
        
        // Handle payments that were not processed
        if ($pmtQueue->count()) {
            foreach ($pmtQueue as $currPmt) {
                $modBills->add((object) [
                    'id' => null,
                    'date' => $currPmt->date,
                    'debit' => round($currPmt->amount, 2),
                    'credit' => 0,
                ]);
            }
        }

        return $modBills; 
    }
}