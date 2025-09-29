<?php

namespace App\Repositories\Focus\customer;

use App\Models\creditnote\CreditNote;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\JournalItem;
use App\Models\transaction\Transaction;

trait CustomerStatement
{
    /**
     * Customer Invoices data
     */
    public function getInvoicesForDataTable($customer_id = 0)
    {
        $q = Invoice::where('customer_id', request('customer_id', $customer_id));
            // ->when(request('project_id'), fn($q) => $q->whereHas('transactions', fn($q) => $q->where('tr_type', 'inv')->where('project_id', request('project_id'))));

        return $q->get();
    }
    
    /**
     * Statement on account transactions
     */
    public function getTransactionsForDataTable($customer_id = 0)
    {
        $params = ['customer_id' => request('customer_id', $customer_id)];
        $q = Transaction::where('customer_id', $params['customer_id']);
        $q->whereHas('account', fn($q) => $q->whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['receivable', 'customer_deposits', 'loan'])));
        $q->where(function ($q) {
            $q->whereHas('invoice', fn($q) => $q->withoutGlobalScope('ins'));
            $q->orWhereHas('deposit', fn($q) => $q->withoutGlobalScope('ins')->whereNull('rel_payment_id'));
            $q->orWhereHas('withholding', fn($q) => $q->withoutGlobalScope('ins'));
            $q->orWhereHas('creditnote', fn($q) => $q->withoutGlobalScope('ins'));
            $q->orWhereHas('debitnote', fn($q) => $q->withoutGlobalScope('ins'));
            $q->orWhereHas('manualjournal', fn($q) => $q->withoutGlobalScope('ins'));
            $q->orwhereHas('manualjournal', fn($q) => $q->withoutGlobalScope('ins')->whereHas('items'));
        });
        $q->with([
            'invoice.project',
            'invoice.quote.project', 
            'deposit.project', 
            'deposit.invoices.project', 
            'deposit.invoices.quotes.project',
            'manualjournal.paid_invoice.project'
        ]);
        
        // on date filter
        if (request('start_date') && request('is_transaction')) {
            $from = date_for_database(request('start_date'));
            $tr_ids = $q->pluck('id')->toArray();
            
            $params = ['id', 'tr_date', 'tr_type', 'note', 'debit', 'credit'];
            $transactions = Transaction::whereIn('id', $tr_ids)->whereBetween('tr_date', [$from, date('Y-m-d')])->get($params);
            // compute balance brought foward as of start date
            $bf_transactions = Transaction::whereIn('id', $tr_ids)->where('tr_date', '<', $from)->get($params);
            $debit_balance = $bf_transactions->sum('debit') - $bf_transactions->sum('credit');
            if ($debit_balance) {
                $record = (object) array(
                    'id' => 0,
                    'tr_date' => date('Y-m-d', strtotime($from . ' - 1 day')),
                    'tr_type' => 'balance',
                    'note' => '** Balance Brought Foward ** ',
                    'debit' => $debit_balance > 0 ? $debit_balance : 0,
                    'credit' => $debit_balance < 0 ? ($debit_balance * -1) : 0,
                );
                // merge brought foward balance with the rest of the transactions
                $transactions = collect([$record])->merge($transactions);
            }
            return $transactions;
        }
        return $q->get();
    }

    public function getTransactionsForMail($customer_id = 0, $start_date = null)
    {
        $params = ['customer_id' => request('customer_id', $customer_id)];
        $q = Transaction::withoutGlobalScopes()
        ->where('ins', auth()->user()->ins)
        ->whereHas('account', function ($q) { 
            $q->whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['receivable', 'loan']));
        })
        ->where(function ($q) use ($params) {
            $q->whereHas('invoice', fn($q) => $q->withoutGlobalScopes()->where($params))
            ->orWhereHas('deposit', fn($q) => $q->withoutGlobalScopes()->where($params))
            ->orWhereHas('withholding', fn($q) => $q->withoutGlobalScopes()->where($params))
            ->orWhereHas('creditnote', fn($q) => $q->withoutGlobalScopes()->where($params))
            ->orWhereHas('debitnote', fn($q) => $q->withoutGlobalScopes()->where($params));
        })
        ->orWhere(function ($q) use ($params) {
            $q->whereHas('manualjournal', fn($q) => $q->withoutGlobalScopes()->where($params))
            ->where('debit', '>', 0);
        });
    
        // on date filter
        if ($start_date) {
            $from = date_for_database($start_date);
            $tr_ids = $q->pluck('id')->toArray();
            
            $params = ['id', 'tr_date', 'tr_type', 'note', 'debit', 'credit'];
            $transactions = Transaction::withoutGlobalScopes()->whereIn('id', $tr_ids)->whereBetween('tr_date', [$from, date('Y-m-d')])->get($params);
            // compute balance brought foward as of start date
            $bf_transactions = Transaction::withoutGlobalScopes()->whereIn('id', $tr_ids)->where('tr_date', '<', $from)->get($params);
            $debit_balance = $bf_transactions->sum('debit') - $bf_transactions->sum('credit');
            if ($debit_balance) {
                $record = (object) array(
                    'id' => 0,
                    'tr_date' => date('Y-m-d', strtotime($from . ' - 1 day')),
                    'tr_type' => 'balance',
                    'note' => '** Balance Brought Foward ** ',
                    'debit' => $debit_balance > 0 ? $debit_balance : 0,
                    'credit' => $debit_balance < 0 ? ($debit_balance * -1) : 0,
                );
                // merge brought foward balance with the rest of the transactions
                $transactions = collect([$record])->merge($transactions);
            }
            return $transactions;
        }
        return $q->get();
    }

    /**
     * Statement on Invoice Data
     */
    public function getStatementForDataTable($customer_id = 0)
    {
        $invoices = Invoice::where('customer_id', request('customer_id', $customer_id))
            ->with(['quote.project', 'payments.paid_invoice.project', 'withholding_payments', 'creditnotes', 'debitnotes'])
            ->get();

        return $this->generateStatement($invoices);
    }

    public function getStatementForMail($customer_id = 0)
    {
        $q = Invoice::withoutGlobalScopes()->where('customer_id', request('customer_id', $customer_id));
        $q->with([
            'payments' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'payments.paid_invoice' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'payments.paid_invoice.account' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'withholding_payments' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'creditnotes' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'debitnotes' => function ($query) {
                $query->withoutGlobalScopes();
            },
        ]);
        // dd($q->get(), $customer_id);
        // 
        return $this->generateStatement($q->get());
    }

    /**
     * Statement On Invoice
     */
    public function generateStatement($invoices = [])
    {
        $i = 0;
        $statement = collect();    
        foreach ($invoices as $invoice) {
            $i++;
            $invoice_id = $invoice->id;
            $tid = gen4tid('Inv-', $invoice->tid);
            $note = $invoice->notes;
            $project = @$invoice->project;
            if (!$project) {
                foreach ($invoice->quotes as $quote) {
                    if (@$quote->project->id == request('project2_id')) {
                        $project = $quote->project;
                        break;
                    }
                }
            }
            // if ($invoice->tid == 1367) dd(request('project2_id'), $invoice, $project);
            $inv_record = (object) array(
                'id' => $i,
                'date' => $invoice->invoicedate,
                'type' => 'invoice',
                'note' => '(' . $tid . ')' . ' ' . $note,
                'debit' => $invoice->total,
                'credit' => 0,
                'invoice_id' => $invoice_id,
                'project_id' => @$project->id,
            );
            // invoice deposit allocations
            $payments = collect();       
            foreach ($invoice->payments as $pmt) {
                $parentPmt = $pmt->paid_invoice;
                if ($parentPmt) {
                    $i++;
                    $reference = $parentPmt->reference;
                    $mode = $parentPmt->payment_mode;
                    $pmtTid = gen4tid('PMT-', $parentPmt->tid);
                    $account = @$parentPmt->account->holder;
                    $amount = numberFormat($parentPmt->amount);
                    $note = "({$tid}) <br> {$pmtTid} - Reference No: {$reference} Mode: {$mode} Account: {$account} Amount: {$amount}";

                    $project = @$parentPmt->relPayment->project;
                    if (!$project) {
                        // check for project in invoices
                        foreach ($parentPmt->invoices as $invoice) {
                            if (@$invoice->project->id == request('project2_id')) {
                                $project = @$invoice->project;
                                break;
                            } else {
                                foreach ($invoice->quotes as $quote) {
                                    if (@$quote->project->id == request('project2_id')) {
                                        $project = $quote->project;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }

                    if ($project) {
                        $projectTid = gen4tid('PRJ-', $project->tid);
                        $projectName = $project->name;
                        $note = "({$projectTid}) - {$projectName} <br>" . $note;
                    }
                    $record = (object) array(
                        'id' => $i,
                        'date' => $parentPmt->date,
                        'type' => 'payment',
                        'note' => $note,
                        'debit' => 0,
                        'credit' => $pmt->paid,
                        'invoice_id' => $invoice_id,
                        'payment_item_id' => $pmt->id,
                        'payment_id' => $parentPmt->id,
                        'project_id' => @$project->id,
                    );
                    $payments->add($record);
                }       
            }           
            // invoice withholdings
            $withholdings = collect();
            foreach ($invoice->withholding_payments as $pmt) {
                $i++;
                $reference = @$pmt->withholding->reference;
                $certificate = @$pmt->withholding->certificate;
                $note = @$pmt->withholding->note;
                $date = @$pmt->withholding->date;
                $record = (object) array(
                    'id' => $i,
                    'date' => $date,
                    'type' => 'withholding',
                    'note' => "({$tid}) {$reference} - {$certificate} - {$note}",
                    'debit' => 0,
                    'credit' => $pmt->paid,
                    'invoice_id' => $invoice_id,
                    'withholding_item_id' => $pmt->id,
                    'withholding_id' => $pmt->withholding->id,
                );
                $withholdings->add($record);
            }  
            // invoice payment withholdings (set on payment allocation)
            foreach ($invoice->payments as $pmt) {
                $parentPmt = $pmt->paid_invoice;
                if ($parentPmt) {
                    $reference = $parentPmt->reference ?: 'N/A';
                    $mode = $parentPmt->payment_mode;
                    $account = @$parentPmt->account->holder;
                    $amount = $parentPmt->amount;
                    $pmt_tid = gen4tid('PMT-', $parentPmt->tid);
                    if ($pmt->wh_vat > 0) {
                        $i++;
                        $certificate = @$pmt->withholding_item->withholding->certificate ?: 'N/A';
                        $note = @$pmt->withholding_item->withholding->note;
                        $record = (object) array(
                            'id' => $i,
                            'date' => $parentPmt->date,
                            'type' => 'payment:wh-vat',
                            'note' => "({$tid}) - {$pmt_tid} Reference: {$reference} Certificate: {$certificate} Note: {$note}",
                            'debit' => 0,
                            'credit' => $pmt->wh_vat,
                            'invoice_id' => $invoice_id,
                            'payment_item_id' => $pmt->id,
                            'project_id' => @$parentPmt->project->id,
                        );
                        $payments->add($record);
                    }
                    if ($pmt->wh_tax > 0) {
                        $i++;
                        $certificate = @$pmt->withholding_item->withholding->certificate ?: 'N/A';
                        $note = @$pmt->withholding_item->withholding->note;
                        $record = (object) array(
                            'id' => $i,
                            'date' => $parentPmt->date,
                            'type' => 'payment:wh-tax',
                            'note' => "({$tid}) - {$pmt_tid} reference: {$reference} certificate: {$certificate} memo: {$note}",
                            'debit' => 0,
                            'credit' => $pmt->wh_tax,
                            'invoice_id' => $invoice_id,
                            'payment_item_id' => $pmt->id,
                            'project_id' => @$parentPmt->project->id,
                        );
                        $payments->add($record);
                    }
                }        
            }   

            // invoice credit notes
            $creditnotes = collect();     
            foreach ($invoice->creditnotes as $cnote) {
                $i++;
                $record = (object) array(
                    'id' => $i,
                    'date' => $cnote->date,
                    'type' => 'credit-note',
                    'note' => '(' . $tid . ')' . ' ' . $cnote->note,
                    'debit' => 0,
                    'credit' => $cnote->total,
                    'invoice_id' => $invoice_id,
                    'creditnote_id' => $cnote->id,
                    'project_id' => (@$invoice->project->id ?: @$invoice->quote->project->id),
                );
                $creditnotes->add($record);
            }   
            // invoice debit notes
            $debitnotes = collect();
            foreach ($invoice->debitnotes as $dnote) {
                $i++;
                $record = (object) array(
                    'id' => $i,
                    'date' => $dnote->date,
                    'type' => 'debit-note',
                    'note' => '(' . $tid . ')' . ' ' . $dnote->note,
                    'dedit' => $dnote->total,
                    'credit' => 0,
                    'invoice_id' => $invoice_id,
                    'debitnote_id' => $dnote->id,
                    'project_id' => (@$invoice->project->id ?: @$invoice->quote->project->id),
                );
                $debitnotes->add($record);
            }   
            $statement->add($inv_record);
            $statement = $statement->merge($payments);
            $statement = $statement->merge($creditnotes);
            $statement = $statement->merge($withholdings);
        }
        return $statement;        
    }

    /**
     * Customer Filtered Aging
     */
    public function agingFilteredBills($customerId, $startDate)
    {
        // customer bills
        $bills = Invoice::query()
        ->where('invoicedate', '<=', $startDate)
        ->where('customer_id', $customerId)
        ->orderBy('invoicedate', 'ASC')
        ->get(['id', 'invoicedate', 'fx_curr_rate', 'total', 'fx_total']);
        // ->sum(\DB::raw("IF(fx_curr_rate = 0 or fx_curr_rate = 1, total, fx_total)"));
        // dd(+$bills, date_for_database(request('start_date')));

        // customer payments
        $pmts = InvoicePayment::query()
        ->where('date', '<=', $startDate)
        ->where('customer_id', $customerId)
        ->whereNull('rel_payment_id')
        ->orderBy('date', 'ASC')
        ->get(['id', 'date', 'amount', 'fx_amount', 'fx_curr_rate']);        
        // ->sum('amount');
        // dd(+$pmts, +$bills, +$bills-$pmts);
        $pmts1 = $pmts;

        // customer credit memos
        $memos = CreditNote::query()
        ->where('date', '<=', $startDate)
        ->where('customer_id', $customerId)
        ->orderBy('date', 'ASC')
        ->get(['id', 'date', 'fx_curr_rate', 'total', 'fx_total', 'is_debit']);  
        // ->sum(\DB::raw("IF(fx_curr_rate = 0 or fx_curr_rate = 1, total, fx_total)"));
        // dd(+$bills, date_for_database(request('start_date')));
        $memos1 = $memos;

        // journal adjustments
        $journalItems = JournalItem::query()
        ->where('customer_id', $customerId)
        ->whereHas('journal', fn($q) => $q->where('date', '<=', $startDate))
        ->whereHas('account', function($q) {
            $q->whereHas('account_type_detail', fn($q) => $q->where('system', 'receivable'));
        })
        ->with('journal')
        ->get(['id', 'customer_id', 'credit', 'debit', 'journal_id']);
        foreach ($journalItems as $item) {
            if ($item->debit > 0) {
                $bills->push(Invoice::make( [
                    'id' => null,
                    'invoicedate' => $item->journal->date,
                    'total' => $item->debit,
                    'fx_curr_rate' => 1,
                    'fx_total' => 0,
                    'is_fx' => false,
                ]));
            } elseif ($item->credit > 0) {
                $pmts->push(InvoicePayment::make( [
                    'id' => null,
                    'date' => $item->journal->date,
                    'amount' => $item->credit,
                    'fx_curr_rate' => 1,
                    'fx_amount' => 0,
                    'is_fx' => false,
                ]));
            }
        }
        if ($journalItems->count()) {
            $bills = $bills->sortBy(fn($item) => $item->invoicedate); 
            $pmts = $pmts->sortBy(fn($item) => $item->date); 
        }

        $modBills = collect();
    
        $pmtBucket = 0;
        $pmtQueue = $pmts;
        $memoQueue = $memos;
        $currPmt = $pmtQueue->shift();
        $currMemo = $memoQueue->shift();

        foreach ($bills as $key => $bill) {
            $debit = round($bill->is_fx? $bill->fx_total : $bill->total, 2);
            $credit = 0;

            // Apply memos to bills
            while ($debit > $credit && $currMemo) {
                $memoAmount = round($currMemo->is_fx? $currMemo->fx_total : $currMemo->total, 2);
                if ($pmtBucket) $memoAmount = 0;

                $billBal = $debit - $credit;
                if ($currMemo->is_debit) {
                    $availAmount = $pmtBucket - $memoAmount;
                } else {
                    $availAmount = $pmtBucket + $memoAmount;
                }
                
                if ($availAmount >= $billBal) {
                    $credit += $billBal;
                    $pmtBucket = $availAmount - $billBal;
                    if ($pmtBucket == 0) {
                        $currMemo = $memoQueue->shift();
                    }
                    break;
                } else {
                    if ($availAmount > 0) $credit += $availAmount;
                    else $debit += -$availAmount;
                    $pmtBucket = 0;
                    $currMemo = $memoQueue->shift();
                }
            }

            // Apply payments to bills 
            while ($debit > $credit && $currPmt) {
                $pmtAmount = round($currPmt->is_fx ? $currPmt->fx_amount : $currPmt->amount, 2);
                if ($pmtBucket) $pmtAmount = 0;

                $billBal = $debit - $credit;
                $availAmount = $pmtBucket + $pmtAmount;
                if ($availAmount >= $billBal) {
                    $credit += $billBal;
                    $pmtBucket = $availAmount - $billBal;
                    if ($pmtBucket == 0) {
                        $currPmt = $pmtQueue->shift();
                    }
                    break; // Bill fully paid
                } else {
                    $credit += $availAmount;
                    $pmtBucket = 0;
                    $currPmt = $pmtQueue->shift();
                }
            }

            $modBills->add((object) [
                'id' => $bill->id,
                'date' => $bill->invoicedate,
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
                    'debit' => 0,
                    'credit' => round($pmt->amount, 2),
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
                    'debit' => 0,
                    'credit' => $pmtBucket,
                ]);
            } else {
                $modBills->add((object) [
                    'id' => null,
                    'date' => $currPmt->date,
                    'debit' => $pmtBucket,
                    'credit' => 0,
                ]);
            }
        }

        // Handle payments that were not processed
        if ($pmtQueue->count()) {
            foreach ($pmtQueue as $currPmt) {
                $pmtAmount = round($currPmt->is_fx? $currPmt->fx_amount : $currPmt->amount, 2);
                $modBills->add((object) [
                    'id' => null,
                    'date' => $currPmt->date,
                    'debit' => 0,
                    'credit' => $pmtAmount,
                ]);
            }
        }

        // Sort bills by date in ascending order
        $modBills = $modBills->sortBy('date')->values();
        
        return $modBills; 
    }
}