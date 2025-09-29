<?php

namespace App\Repositories;

use App\Models\account\Account;
use App\Models\equipment\Assetequipment;
use App\Models\manualjournal\Journal;
use App\Models\project\Project;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;

trait Accounting
{
    /**
     * Error Handler
     */
    private function accountingError($msg='')
    {
        throw \Illuminate\Validation\ValidationException::withMessages([$msg]);
    }
    
    /**
     * Customer Sale Return
     * @param object $sale_return
     */
    public function post_sale_return($sale_return)
    {
        $tr_category = Transactioncategory::where('code', 'stock')->first(['id', 'code']);
        $dr_data = [
            'tid' => Transaction::max('tid')+1,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $sale_return->date,
            'due_date' => $sale_return->date,
            'user_id' => $sale_return->user_id,
            'note' => $sale_return->note,
            'ins' => $sale_return->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $sale_return->id,
            'user_type' => 'customer',
            'customer_id' => $sale_return->customer_id,
            'sale_return_id' => $sale_return->id,
            'is_primary' => 1,
        ];

        $stock_account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'inventory_asset'))->first(['id']);
        if (!$stock_account) $this->accountingError('Inventory Asset Account required!');
        $cog_account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'cost_of_goods_sold'))->first(['id']);
        if (!$cog_account) $this->accountingError('Cost of Goods Sold (COGS) Account required!');

        foreach ($sale_return->items as $key => $item) {
            $productvarId = $item->productvar->id;
            $name = $item->productvar->name;
            // debit Inventory Asset Account
            $dr_data = array_replace($dr_data, [
                'note' => $name,
                'account_id' => $item->productvar->asset_account_id ?: $stock_account->id,
                'debit' => $item->amount,
                'productvar_id' => $productvarId,
            ]);
            Transaction::create($dr_data);
            unset($dr_data['debit'], $dr_data['is_primary']);
            
            // credit COGS Expense Account
            $cr_data = array_replace($dr_data, [
                'note' => $name,
                'account_id' => $item->productvar->exp_account_id ?: $cog_account->id,
                'credit' =>  $item->amount,
                'productvar_id' => $productvarId,
            ]);
            Transaction::create($cr_data);
        }
    }

    /**
     * Inventory Stock Issue
     * 
     * @param object $stock_issue
     */
    public function post_stock_issue($stock_issue)
    {
        $tr_category = Transactioncategory::where('code', 'stock')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::max('tid')+1,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $stock_issue->date,
            'due_date' => $stock_issue->date,
            'user_id' => $stock_issue->user_id,
            'ins' => $stock_issue->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $stock_issue->id,
            'user_type' => $stock_issue->customer_id? 'customer':'employee',
            'customer_id' => $stock_issue->customer_id,
            'stock_issue_id' => $stock_issue->id,
            'is_primary' => 1,
        ];

        $stock_account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'inventory_asset'))->first(['id']);
        if (!$stock_account) $this->accountingError('Inventory Asset Account required!');
        $cog_account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'cost_of_goods_sold'))->first(['id']);
        if (!$cog_account) $this->accountingError('Cost of Goods Sold (COGS) Account required!');
        $wip_account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'work_in_progress'))->first(['id']);
        if (!$wip_account) $this->accountingError('Work In Progress (WIP) Account required!');

        if (!array_filter([$stock_issue->project, $stock_issue->customer, $stock_issue->employee])) {
            $this->accountingError('Issuance project, customer or employee required!');
        }

        foreach ($stock_issue->items as $key => $item) {
            $productvarId = $item->productvar->id;
            $note = $item->productvar->name;
            $subtotal = $item->amount;

            $invAccount = Account::where('id', $item->productvar->asset_account_id)
            ->whereHas('account_type_detail', fn($q) => $q->where('system', 'inventory_asset'))
            ->first(['id']);

            // credit Inventory Asset Account
            $cr_data = array_replace($cr_data, [
                'note' => $note,
                'account_id' => @$invAccount->id ?: $stock_account->id,
                'credit' =>  $subtotal,
                'productvar_id' => $productvarId,
            ]);
            Transaction::create($cr_data);
            unset($cr_data['credit'], $cr_data['is_primary']);

            // debit WIP Account
            if ($stock_issue->project) {
                $projectWip = Account::where('id', $stock_issue->project->wip_account_id)
                    ->whereHas('account_type_detail', fn($q) => $q->where('system', 'work_in_progress'))
                    ->first(['id']);

                $dr_data = array_replace($cr_data, [
                    'note' => $note,
                    'account_id' => @$projectWip->id ?: $wip_account->id,
                    'debit' =>  $subtotal,
                    'productvar_id' => $productvarId,
                    'project_id' => $stock_issue->project->id,
                ]);
                Transaction::create($dr_data);
            } else {
                // debit COGS Account
                if ($stock_issue->customer) {
                    $expAcc = Account::where('id', $item->productvar->exp_account_id)
                    ->whereHas('account_type_detail', fn($q) => $q->where('system_rel', 'cogs'))
                    ->first(['id']);

                    $dr_data = array_replace($cr_data, [
                        'note' => $note,
                        'account_id' => @$expAcc->id ?: $cog_account->id,
                        'debit' =>  $subtotal,
                        'productvar_id' => $productvarId,
                    ]);
                    Transaction::create($dr_data);
                } elseif ($stock_issue->employee) {
                    // debit selected Expense Account
                    $dr_data = array_replace($cr_data, [
                        'note' => $note,
                        'account_id' => $stock_issue->account_id,
                        'debit' =>  $subtotal,
                        'productvar_id' => $productvarId,
                    ]);
                    Transaction::create($dr_data);
                }
            }
        }
        $diff = Transaction::where('tid', $cr_data['tid'])->sum(\DB::raw('debit-credit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }

    /**
     * Inventory Stock Adjustment
     * 
     * @param object $stock_adj
     */
    public function post_stock_adjustment($stock_adj)
    {
        $tr_category = Transactioncategory::where('code', 'stock')->first(['id', 'code']);
        $tr_data = [
            'tid' => Transaction::max('tid')+1,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $stock_adj->date,
            'due_date' => $stock_adj->date,
            'user_id' => $stock_adj->user_id,
            'ins' => $stock_adj->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $stock_adj->id,
            'user_type' => 'employee',
            'stock_adj_id' => $stock_adj->id,
            'is_primary' => 1,
        ];

        $invAccount = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'inventory_asset'))->first(['id']);
        if (!$invAccount) $this->accountingError('Inventory Asset Account required!');
        $cog_account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'cost_of_goods_sold'))->first(['id']);
        if (!$cog_account) $this->accountingError('Cost of Goods Sold (COGS) Account required!');

        foreach ($stock_adj->items as $item) {
            $productvarId = $item->productvar->id;
            $note = $item->productvar->name;
            $subtotal = abs(+$item->amount);
            if ($stock_adj->total > 0) {
                $productInvAccount = Account::where('id', $item->productvar->asset_account_id)->whereHas('account_type_detail', fn($q) => $q->where('system', 'inventory_asset'))->first(['id']);

                // debit Inventory Asset Account
                $tr_data = array_replace($tr_data, [
                    'note' => $note,
                    'account_id' => @$productInvAccount->id ?: $invAccount->id,
                    'debit' =>  $subtotal,
                    'productvar_id' => $productvarId,
                ]);
                Transaction::create($tr_data);
                unset($tr_data['debit'], $tr_data['is_primary']);

                // credit COGS Account
                $tr_data = array_replace($tr_data, [
                    'note' => $note,
                    'account_id' => $stock_adj->account_id ?: $cog_account->id,
                    'credit' =>  $subtotal,
                    'productvar_id' => $productvarId,
                ]);
                Transaction::create($tr_data);
            } else {
                $productInvAccount = Account::where('id', $item->productvar->asset_account_id)->whereHas('account_type_detail', fn($q) => $q->where('system', 'inventory_asset'))->first(['id']);

                // credit Inventory Asset Account
                $tr_data = array_replace($tr_data, [
                    'note' => $note,
                    'account_id' => @$productInvAccount->id ?: $invAccount->id,
                    'credit' =>  $subtotal,
                    'productvar_id' => $productvarId,
                ]);
                Transaction::create($tr_data);
                unset($tr_data['credit'], $tr_data['is_primary']);
    
                // debit COGS Account
                $tr_data = array_replace($tr_data, [
                    'note' => $note,
                    'account_id' => $stock_adj->account_id ?: $cog_account->id,
                    'debit' =>  $subtotal,
                    'productvar_id' => $productvarId,
                ]);
                Transaction::create($tr_data);
            }
        }
        $diff = Transaction::where('tid', $tr_data['tid'])->sum(\DB::raw('debit-credit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }

    /**
     * Ledger Account Opening Balance
     * @param object $account
     */
    public function post_ledger_opening_balance($manual_journal)
    {
        $tr_category = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $tr_data = [
            'tid' => Transaction::max('tid')+1,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $manual_journal->date,
            'due_date' => $manual_journal->date,
            'user_id' => $manual_journal->user_id,
            'note' => $manual_journal->note,
            'ins' => $manual_journal->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $manual_journal->id,
            'user_type' => 'company',
            'man_journal_id' => $manual_journal->id,
            'is_primary' => 1,
        ];

        $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'retained_earnings'))->first(['id']);
        if (!$account) $this->accountingError('Retained Earnings Account required!');
        $account_type = $manual_journal->ledger_account->account_type;
        if (in_array($account_type, ['Asset', 'Expense'])) {
            // debit Asset/Expense Account 
            $tr_data = array_replace($tr_data, [
                'account_id' => $manual_journal->account_id, 
                'debit' => $manual_journal->opening_balance,
            ]);
            Transaction::create($tr_data);
            unset($tr_data['debit'], $tr_data['is_primary']);

            // credit Retained Earnings Account
            $tr_data = array_replace($tr_data, [
                'account_id' => $account->id, 
                'credit' => $manual_journal->opening_balance,
            ]);
            Transaction::create($tr_data);
        } else {
            // credit Income/Liability/Equity Account
            $tr_data = array_replace($tr_data, [
                'account_id' => $manual_journal->account_id, 
                'credit' => $manual_journal->opening_balance,
            ]);
            Transaction::create($tr_data);
            unset($tr_data['credit'], $tr_data['is_primary']);

            // debit Retained Earnings Account
            $tr_data = array_replace($tr_data, [
                'account_id' => $account->id, 
                'debit' => $manual_journal->opening_balance,
            ]);
            Transaction::create($tr_data);
        }
        $diff = Transaction::where('tid', $tr_data['tid'])->sum(\DB::raw('debit-credit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }

    /**
     * Customer Opening Balance
     * @param object $manual_journal
     */
    public function post_customer_opening_balance($journal)
    {
        $tr_category = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $tr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $journal->account_id,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $journal->date,
            'due_date' => $journal->date,
            'user_id' => $journal->user_id,
            'note' => $journal->note,
            'ins' => $journal->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $journal->id,
            'user_type' => 'customer',
            'is_primary' => 1,
            'customer_id' => $journal->customer_id,
            'man_journal_id' => $journal->id,
        ];
        $currency = @$journal->ledger_account->currency;
        if (!$currency) $this->accountingError('Accounts Receivable currency required');
        if ($currency->rate == 1) {
            // debit Accounts Receivable (Debtor)
            $dr_data = array_replace($tr_data, [
                'debit' => $journal->open_balance
            ]);
            Transaction::create($dr_data);
            unset($tr_data['is_primary']);
    
            // credit Retained Earnings (Equity)
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'retained_earnings'))->first(['id']);
            if (!$account) $this->accountingError('Retained Earnings Account required!');
            $cr_data = array_replace($tr_data, [
                'account_id' => $account->id, 
                'credit' => $journal->open_balance
            ]);
            Transaction::create($cr_data);
        } else {
            // debit Accounts Receivable (Debtor)
            $dr_data = array_replace($tr_data, [
                'debit' => round($journal->open_balance * $currency->rate, 4),
                'fx_debit' => $journal->open_balance,
                'fx_curr_rate' => $currency->rate,
            ]);
            Transaction::create($dr_data);
            unset($tr_data['is_primary']);
    
            // credit Retained Earnings (Equity)
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'retained_earnings'))->first(['id']);
            if (!$account) $this->accountingError('Retained Earnings Account required!');
            $cr_data = array_replace($tr_data, [
                'account_id' => $account->id, 
                'credit' => round($journal->open_balance * $currency->rate, 4),
                'fx_credit' => $journal->open_balance,
                'fx_curr_rate' => $currency->rate,
            ]);
            Transaction::create($cr_data);
        }
        
        $diff = Transaction::where('tid', $tr_data['tid'])->sum(\DB::raw('debit-credit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }

    /**
     * Withholding Transaction
     * @param object $withholding
     */
    public function post_withholding($withholding)
    {
        // credit Accounts Receivable
        $tr_category = Transactioncategory::where('code', 'wht')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $withholding->customer->ar_account_id,
            'trans_category_id' => $tr_category->id,
            'credit' => $withholding->amount,
            'tr_date' => $withholding->tr_date,
            'due_date' => $withholding->tr_date,
            'user_id' => $withholding->user_id,
            'note' => $withholding->note,
            'ins' => $withholding->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $withholding->id,
            'user_type' => 'customer',
            'is_primary' => 1,
            'customer_id' => $withholding->customer_id,
            'wht_id' => $withholding->id,
        ];
        Transaction::create($cr_data);
        unset($cr_data['credit'], $cr_data['is_primary']);

        // debit Withholding Account (liability)
        $certificate_type = $withholding->certificate;
        if ($certificate_type == 'vat') {
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'withholding_vat_payable'))->first(['id']);
            if (!$account) $this->accountingError('Withholding VAT Payable Account required!');
            $dr_data = array_replace($cr_data, [
                'account_id' => $account->id,
                'debit' => $withholding->amount
            ]);
            Transaction::create($dr_data);
        } elseif ($certificate_type == 'tax') {
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'withholding_tax_payable'))->first(['id']);
            if (!$account) $this->accountingError('Withholding TAX Payable Account required!');
            $dr_data = array_replace($cr_data, [
                'account_id' => $account->id,
                'debit' => $withholding->amount
            ]);
            Transaction::create($dr_data);
        }
    }

    /**
     * Credit Note and Debit Note Transaction
     * 
     * @param object $model
     */
    public function post_creditnote_debitnote($model)
    {  
        $tr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $model->customer->ar_account_id,
            'tr_ref' => $model->id,
            'tr_date' => $model->date,
            'due_date' => $model->date,
            'user_id' => $model->user_id,
            'note' => $model->note,
            'ins' => $model->ins,
            'user_type' => 'customer',
            'is_primary' => 1,
            'customer_id' => $model->customer_id,
            'classlist_id' => $model->classlist_id,
            'fx_curr_rate' => $model->fx_curr_rate,
        ];

        // Debit Note
        if ($model->is_debit) {
            $tr_category = Transactioncategory::where('code', 'dnote')->first(['id', 'code']);
            $tr_data = array_replace($tr_data, [
                'trans_category_id' => $tr_category->id, 
                'tr_type' => $tr_category->code,
                'dnote_id' => $model->id,
            ]);
            // debit Account Receivables
            $dr_data = array_replace($tr_data, [
                'debit' => $model->fx_curr_rate > 1? $model->fx_total : $model->total,
                'fx_debit' => $model->fx_curr_rate > 1? $model->total : 0, 
            ]);
            Transaction::create($dr_data);
            unset($tr_data['is_primary']);
            // credit Revenue Account (Income)
            $cr_data = array_replace($tr_data, [
                'account_id' => $model->invoice->account_id,
                'credit' => $model->fx_curr_rate > 1?  $model->fx_subtotal : $model->subtotal,
                'fx_credit' => $model->fx_curr_rate > 1? $model->subtotal : 0,
            ]);
            Transaction::create($cr_data);
            // credit VAT Payable
            if ($model->tax > 0) {
                $vat_account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'vat_payable'))->first(['id']);
                if (!$vat_account) $this->accountingError('VAT Payable Account required!');
                $cr_data = array_replace($tr_data, [
                    'account_id' => $vat_account->id,
                    'credit' => $model->fx_curr_rate > 1? $model->fx_tax : $model->tax,
                    'fx_credit' => $model->fx_curr_rate > 1? $model->tax : 0,
                ]);
                Transaction::create($cr_data);
            }
        } 
        // Credit Note
        else {
            $tr_category = Transactioncategory::where('code', 'cnote')->first(['id', 'code']);
            $tr_data = array_replace($tr_data, [
                'trans_category_id' => $tr_category->id, 
                'tr_type' => $tr_category->code,
                'cnote_id' => $model->id,
            ]);
            // credit Accounts Receivable
            $cr_data = array_replace($tr_data, [
                'credit' => $model->fx_curr_rate > 1? $model->fx_total : $model->total,
                'fx_credit' => $model->fx_curr_rate > 1? $model->total : 0,
            ]);
            Transaction::create($cr_data);
            unset($tr_data['is_primary']);
            // debit Revenue Account (Income)
            $dr_data = array_replace($tr_data, [
                'account_id' => $model->invoice->account_id,
                'debit' => $model->fx_curr_rate > 1? $model->fx_subtotal : $model->subtotal,
                'fx_debit' => $model->fx_curr_rate > 1? $model->subtotal : 0,
            ]);
            Transaction::create($dr_data);
            // debit VAT Payable
            if ($model->tax > 0) {
                $vat_account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'vat_payable'))->first(['id']);
                if (!$vat_account) $this->accountingError('VAT Payable Account required!');
                $dr_data = array_replace($tr_data, [
                    'account_id' => $vat_account->id,
                    'debit' => $model->fx_curr_rate > 1? $model->fx_tax : $model->tax,
                    'fx_debit' => $model->fx_curr_rate > 1? $model->tax : 0,
                ]);
                Transaction::create($dr_data);
            }

            /** Issue refund */
            if ($model->account_id) {
                // debit Accounts Receivable
                $dr_data = array_replace($tr_data, [
                    'debit' => $model->fx_curr_rate > 1? $model->fx_total : $model->total,
                    'fx_debit' => $model->fx_curr_rate > 1? $model->total : 0,
                ]);
                Transaction::create($dr_data);
                // credit Bank Account 
                $cr_data = array_replace($tr_data, [
                    'account_id' => $model->account_id,
                    'credit' => $model->fx_curr_rate > 1? $model->fx_total : $model->total,
                    'fx_credit' => $model->fx_curr_rate > 1? $model->total : 0,
                ]);
                Transaction::create($cr_data);
            }
        }

        /** Foreign Gain or Loss Adjustment */
        if ($model->fx_gain > 0) {
            // credit Foreign Gain Account (Revenue)
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'foreign_currency_gain'))->first(['id']);
            if (!$account) $this->accountingError('Foreign Currency Gain Account required!');
            $cr_data = array_replace($tr_data, [
                'account_id' => $account->id,
                'credit' => $model->fx_gain,
            ]);    
            Transaction::create($cr_data);
            // debit Accounts Receivable
            $cr_data = array_replace($tr_data, [
                'debit' => $model->fx_gain,
            ]);
            Transaction::create($cr_data);
        } 
        elseif ($model->fx_loss > 0) {
            // debit Foreign Loss Account (Expense)
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'foreign_currency_loss'))->first(['id']);
            if (!$account) $this->accountingError('Foreign Currency Loss Account required!');
            $dr_data = array_replace($tr_data, [
                'account_id' => $account->id,
                'debit' => $model->fx_loss,
            ]);    
            Transaction::create($dr_data);
            // credit Accounts Receivable
            $cr_data = array_replace($tr_data, [
                'credit' => $model->fx_loss,
            ]);
            Transaction::create($cr_data);
        }

        $diff = Transaction::where('tid', $tr_data['tid'])->sum(\DB::raw('debit-credit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }

    /**
     * Invoice Transaction
     * @param object $invoice
     * 
     * When fx_curr_rate > 1, fx_total is the functional currency else total
     */
    public function post_invoice($invoice)
    {
        if (!$invoice->customer->ar_account_id) {
            $this->accountingError("Mapped customer - Account Receivable (AR) account required!");
        }

        $tr_category = Transactioncategory::where('code', 'inv')->first(['id', 'code']);
        $tr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $invoice->customer->ar_account_id,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $invoice->invoicedate,
            'due_date' => $invoice->invoiceduedate,
            'user_id' => $invoice->user_id,
            'note' => $invoice->notes,
            'ins' => $invoice->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $invoice->id,
            'user_type' => 'customer',
            'is_primary' => 1,
            'customer_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id,
            'fx_curr_rate' => $invoice->fx_curr_rate,
            'classlist_id' => $invoice->classlist_id,
        ];
        // debit Accounts Receivable
        $tr_data = array_replace($tr_data, [
            'debit' => $invoice->fx_curr_rate > 1? $invoice->fx_total : $invoice->total, 
            'fx_debit' => $invoice->fx_curr_rate > 1? $invoice->total : 0, 
        ]);
        Transaction::create($tr_data);
        unset($tr_data['debit'], $tr_data['fx_debit'], $tr_data['is_primary']);

        // credit Revenue Account (Income)
        $tr_data = array_replace($tr_data, [
            'account_id' => $invoice->account_id,
            'credit' => $invoice->fx_curr_rate > 1? $invoice->fx_subtotal : $invoice->subtotal, 
            'fx_credit' => $invoice->fx_curr_rate > 1? $invoice->subtotal : 0, 
        ]);
        Transaction::create($tr_data);

        // credit VAT Payable
        if ($invoice->tax > 0) {
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'vat_payable'))->first(['id']);
            if (!$account) $this->accountingError('VAT Payable Account required!');
            $tr_data = array_replace($tr_data, [
                'account_id' => $account->id,
                'credit' => $invoice->fx_curr_rate > 1? $invoice->fx_tax : $invoice->tax, 
                'fx_credit' => $invoice->fx_curr_rate > 1? $invoice->tax : 0, 
            ]);
            Transaction::create($tr_data);
        }
        unset($tr_data['credit'], $tr_data['fx_credit']);

        // Move Direct Expenses from WIP to COGS
        // credit WIP, debit COG
        $quote_ids = $invoice->products->pluck('quote_id')->toArray();
        $initProjects = Project::whereHas('quotes', fn($q) => $q->whereIn('quotes.id', $quote_ids))->get();
        $projects = $initProjects;
        if ($invoice->project && !in_array($invoice->project->id, $projects->pluck('id')->toArray())) {
            $projects = collect()->merge($initProjects)->merge([$invoice->project]);
        }

        if ($projects->count()) {
            $wip_account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'work_in_progress'))->first(['id']);
            if (!$wip_account) $this->accountingError('Work In Progress Account required!');
            $cog_account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'cost_of_goods_sold'))->first(['id']);
            if (!$cog_account) $this->accountingError('Cost of Goods Sold Account required!');

            // Post using valuations
            $jobValuation = $invoice->jobValuation;
            if ($jobValuation) {
                $projects = []; // unset projects
                $tr_data_arr = [];
                foreach ($jobValuation->valuatedExps as $expItem) {
                    $subtotal = $expItem->total_valuated;
                    $note = $expItem->product_name;
                    $purchaseItemId = $expItem->expitem_id;
                    $project = $expItem->project;
                    $projectWip = Account::where('id', $project->wip_account_id)->whereHas('account_type_detail', fn($q) => $q->where('system', 'work_in_progress'))->first(['id']);
                    $wip_account_id = @$projectWip->id ?: $wip_account->id;
                    $cog_account_id = $cog_account->id;

                    $prodVariation = $expItem->productvariation;
                    $purchaseItem = $expItem->purchaseItem;
                    $casualWage = $expItem->casualRemun;
                    if ($prodVariation) {
                        $item_cog_account = Account::where('id', $prodVariation->exp_account_id)->whereHas('account_type_detail', fn($q) => $q->where('system_rel', 'cogs'))->first(['id']);
                        if ($item_cog_account) $cog_account_id = $item_cog_account->id;
                    } elseif ($purchaseItem) {
                        $item_cog_account = Account::where('id', $purchaseItem->item_id)->whereHas('account_type_detail', fn($q) => $q->where('system_rel', 'cogs'))->first(['id']);
                        if ($item_cog_account) $cog_account_id = $item_cog_account->id;
                    } elseif ($casualWage) {
                        $item_cog_account = Account::where('id', $casualWage->exp_account_id)->whereHas('account_type_detail', fn($q) => $q->where('system_rel', 'cogs'))->first(['id']);
                        if ($item_cog_account) $cog_account_id = $item_cog_account->id;
                    }
                    $productvarId = @$prodVariation->id;
                    
                    // credit WIP, debit COG
                    $tr_data_arr[] = array_replace($tr_data, ['account_id' => $wip_account_id, 'debit' => 0, 'credit' => $subtotal, 'project_id' => $project->id, 'purchase_item_id' => $purchaseItemId, 'productvar_id' => $productvarId, 'note' => $note]);
                    $tr_data_arr[] = array_replace($tr_data, ['account_id' => $cog_account_id, 'debit' => $subtotal, 'credit' => 0, 'project_id' => $project->id, 'purchase_item_id' => $purchaseItemId, 'productvar_id' => $productvarId, 'note' => $note]);
                }
                Transaction::insert($tr_data_arr);
            } 

            // Post without valuations
            foreach ($projects as $project) {
                $projectWip = Account::where('id', $project->wip_account_id)->whereHas('account_type_detail', fn($q) => $q->where('system', 'work_in_progress'))->first(['id']);
                $wip_account_id = @$projectWip->id ?: $wip_account->id;

                // Purchase/Expense Items
                $purchaseItems = $project->purchase_items()
                ->whereDoesntHave('transactions', fn($q) => $q->whereNotNull('invoice_id'))
                ->get();
                foreach ($purchaseItems as $item) {
                    $item_id = $item->id;
                    $note = $item->description;
                    $subtotal = $item->qty * $item->rate;
                    if ($item->purchase && !$item->purchase->is_tax_exc) $subtotal -= $item->taxrate;
                    if ($subtotal > 0) {
                        $cog_account_id = $cog_account->id;
                        if (in_array($item->type, ['Stock', 'Asset'])) {
                            $expAccountId = (int) @$item->productvariation->exp_account_id;
                            $item_cog_account = Account::where('id', $expAccountId)->whereHas('account_type_detail', fn($q) => $q->where('system_rel', 'cogs'))->first(['id']);
                            if ($item_cog_account) $cog_account_id = $item_cog_account->id;
                        } else {
                            $item_cog_account = Account::where('id', $item->item_id)->whereHas('account_type_detail', fn($q) => $q->where('system_rel', 'cogs'))->first(['id']);
                            if ($item_cog_account) $cog_account_id = $item_cog_account->id;
                        }
                        $productvarId = $item->type == 'Stock'? ($item->item_id ?: null) : null;

                        // credit WIP, debit COG
                        $tr_data_arr = [];
                        $tr_data_arr[] = array_replace($tr_data, ['account_id' => $wip_account_id, 'debit' => 0, 'credit' => $subtotal, 'note' => $note, 'purchase_item_id' => $item_id, 'project_id' => $project->id, 'productvar_id' => $productvarId]);
                        $tr_data_arr[] = array_replace($tr_data, ['account_id' => $cog_account_id, 'debit' => $subtotal, 'credit' => 0, 'note' => $note, 'purchase_item_id' => $item_id, 'project_id' => $project->id, 'productvar_id' => $productvarId]);
                        Transaction::insert($tr_data_arr);
                    }
                }

                // Goods Receive Note Items
                $grnItems = $project->grn_items()
                ->whereDoesntHave('transactions', fn($q) => $q->whereNotNull('invoice_id'))
                ->with('purchaseorder_item')
                ->get();
                foreach ($grnItems as $item) {
                    $item_id = $item->id;
                    $note = $item->purchaseorder_item->description;
                    $subtotal = $item->qty * $item->rate;
                    if ($subtotal > 0) {
                        $cog_account_id = $cog_account->id;
                        if (in_array($item->purchaseorder_item->type, ['Stock', 'Asset'])) {
                            $expAccountId = (int) @$item->productvariation->exp_account_id;
                            $item_cog_account = Account::where('id', $expAccountId)->whereHas('account_type_detail', fn($q) => $q->where('system_rel', 'cogs'))->first(['id']);
                            if ($item_cog_account) $cog_account_id = $item_cog_account->id;
                        } 
                        $productvarId = $item->purchaseorder_item->type == 'Stock'? 
                            ($item->purchaseorder_item->product_id ?: null) : null;

                        // credit WIP, debit COG
                        $tr_data_arr = [];
                        $tr_data_arr[] = array_replace($tr_data, ['account_id' => $wip_account_id, 'debit' => 0, 'credit' => $subtotal, 'grn_item_id' => $item_id, 'project_id' => $project->id, 'productvar_id' => $productvarId, 'note' => $note]);
                        $tr_data_arr[] = array_replace($tr_data, ['account_id' => $cog_account_id, 'debit' => $subtotal, 'credit' => 0, 'grn_item_id' => $item_id, 'project_id' => $project->id, 'productvar_id' => $productvarId, 'note' => $note]);
                        Transaction::insert($tr_data_arr);
                    }
                }
            }
        }
        
        $diff = Transaction::where('tid', $tr_data['tid'])->sum(\DB::raw('debit-credit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }
    
    /**
     * Invoice Deposit Transaction
     * @param object $invoice_deposit
     */
    public function post_invoice_deposit($invoice_deposit)
    {
        $fx_invoice_total = 0;
        $fx_gain_total = 0;
        $fx_loss_total = 0;
        foreach ($invoice_deposit->items as $item) {
            if ($item->invoice) {
                $fx_invoice_total += round($item['paid'] * $item->invoice->fx_curr_rate, 4);
                $fx_gain_total += $item->fx_gain;
                $fx_loss_total += $item->fx_loss;
            }
        }
        $deposit_amount = $invoice_deposit->amount;
        $fx_deposit_amount = $invoice_deposit->fx_amount;
        $fx_rate = $invoice_deposit->fx_curr_rate;
        if ($fx_rate > 1 && $fx_invoice_total == 0) {
            $fx_invoice_total = round($deposit_amount*$fx_rate,4);
        }
        
        $tr_category = Transactioncategory::where('code', 'dep')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $invoice_deposit->customer->ar_account_id,
            'trans_category_id' => $tr_category->id,
            'credit' => $fx_rate > 1? $fx_invoice_total : $deposit_amount,
            'fx_credit' => $fx_rate > 1? $deposit_amount : 0,
            'fx_curr_rate' => $fx_rate,
            'tr_date' => $invoice_deposit->date,
            'due_date' => $invoice_deposit->date,
            'user_id' => $invoice_deposit->user_id,
            'note' => ($invoice_deposit->note ?: "{$invoice_deposit->payment_mode} - {$invoice_deposit->reference}"),
            'ins' => $invoice_deposit->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $invoice_deposit->id,
            'user_type' => 'customer',
            'is_primary' => 1,
            'customer_id' => $invoice_deposit->customer_id,
            'deposit_id' => $invoice_deposit->id,
        ];

        /**
         * Allocation of advance payment
         */
        if ($invoice_deposit->is_advance_allocation) {
            // credit Receivables (Debtors)
            Transaction::create($cr_data);
            unset($cr_data['credit'], $cr_data['fx_credit'], $cr_data['is_primary']);
            
            // debit Customer Deposits Account (Liability)
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'customer_deposits'))->first(['id']);
            if (!$account) $this->accountingError('Customer Deposits Account required!');
            $dr_data = array_replace($cr_data, [
                'account_id' => $account->id,
                'debit' => $fx_rate > 1? $fx_deposit_amount : $deposit_amount,
                'fx_debit' => $fx_rate > 1? $deposit_amount : 0,
            ]);    
            Transaction::create($dr_data);

            /** Foreign Gain or Loss Adjustment */
            // credit Foreign Gain Account (Revenue)
            if ($fx_gain_total > 0) {
                $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'foreign_currency_gain'))->first(['id']);
                if (!$account) $this->accountingError('Foreign Currency Gain Account required!');
                $cr_data = array_replace($cr_data, [
                    'account_id' => $account->id,
                    'credit' => $fx_gain_total,
                ]);    
                Transaction::create($cr_data);
            } elseif ($fx_loss_total > 0) {
                // debit Foreign Loss Account (Expense)
                $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'foreign_currency_loss'))->first(['id']);
                if (!$account) $this->accountingError('Foreign Currency Loss Account required!');
                $dr_data = array_replace($cr_data, [
                    'account_id' => $account->id,
                    'debit' => $fx_loss_total,
                ]);    
                Transaction::create($dr_data);
            }
        } else {
            /**
             * Advance Payment
             */
            if ($invoice_deposit->payment_type == 'advance_payment') {
                // credit Customer Deposits Account (Liability)
                $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'customer_deposits'))->first(['id']);
                if (!$account) $this->accountingError('Customer Deposits Account required!');
                $cr_data = array_replace($cr_data, [
                    'account_id' => $account->id,
                    'credit' => $fx_rate > 1? $fx_deposit_amount : $deposit_amount,
                    'fx_credit' => $fx_rate > 1? $deposit_amount : 0,
                ]);    
                Transaction::create($cr_data);
                unset($cr_data['credit'], $cr_data['fx_credit'], $cr_data['is_primary']);
                
                // debit bank (Cash Account)
                $dr_data = array_replace($cr_data, [
                    'account_id' => $invoice_deposit->account_id,
                    'debit' => $fx_rate > 1? $fx_deposit_amount : $deposit_amount,
                    'fx_debit' => $fx_rate > 1? $deposit_amount : 0,
                ]);    
                Transaction::create($dr_data);
            } else {
                // credit Accounts Receivable
                Transaction::create($cr_data);
                unset($cr_data['credit'], $cr_data['fx_credit'], $cr_data['is_primary']);
                            
                // debit Bank Account
                $dr_data = array_replace($cr_data, [
                    'account_id' => $invoice_deposit->account_id,
                    'debit' => $fx_rate > 1? $fx_deposit_amount : $deposit_amount,
                    'fx_debit' => $fx_rate > 1? $deposit_amount : 0,
                ]);    
                Transaction::create($dr_data);

                /** Foreign Gain or Loss Adjustment */
                // credit Foreign Gain Account (Revenue)
                if ($fx_gain_total > 0) {
                    $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'foreign_currency_gain'))->first(['id']);
                    if (!$account) $this->accountingError('Foreign Currency Gain Account required!');
                    $cr_data = array_replace($cr_data, [
                        'account_id' => $account->id,
                        'credit' => $fx_gain_total,
                    ]);    
                    Transaction::create($cr_data);
                } elseif ($fx_loss_total > 0) {
                    // debit Foreign Loss Account (Expense)
                    $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'foreign_currency_loss'))->first(['id']);
                    if (!$account) $this->accountingError('Foreign Currency Loss Account required!');
                    $dr_data = array_replace($cr_data, [
                        'account_id' => $account->id,
                        'debit' => $fx_loss_total,
                    ]);    
                    Transaction::create($dr_data);
                }
            }
        }
        $diff = Transaction::where('tid', $cr_data['tid'])->sum(\DB::raw('credit-debit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }

    /**
     * Supplier Opening Balance 
     * @param object $manual_journal
     */
    public function post_supplier_opening_balance($journal)
    {
        $tr_category = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $tr_data = [
            'tid' => Transaction::max('tid') + 1,
            'account_id' => $journal->account_id,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $journal->date,
            'due_date' => $journal->date,
            'user_id' => $journal->user_id,
            'note' => $journal->note,
            'ins' => $journal->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $journal->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'supplier_id' => $journal->supplier_id,
            'man_journal_id' => $journal->id,
        ];
        $currency = @$journal->ledger_account->currency;
        if (!$currency) $this->accountingError('Accounts Payable currency required');
        if ($currency->rate == 1) {
            // credit Accounts Payable (Creditor)
            $cr_data = array_replace($tr_data, [
                'credit' => $journal->open_balance,
            ]);
            Transaction::create($cr_data);
            unset($tr_data['is_primary']);
    
            // debit Retained Earnings (Equity)
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'retained_earnings'))->first(['id']);
            if (!$account) $this->accountingError('Retained Earnings Account required!');
            $dr_data = array_replace($tr_data, ['account_id' => $account->id, 'debit' => $journal->open_balance]);
            Transaction::create($dr_data);
        } else {
            // credit Accounts Payable (Creditor)
            $cr_data = array_replace($tr_data, [
                'credit' => round($journal->open_balance * $currency->rate, 4),
                'fx_credit' => $journal->open_balance,
                'fx_curr_rate' => $currency->rate,
            ]);
            Transaction::create($cr_data);
            unset($tr_data['is_primary']);

            // debit Retained Earnings (Equity)
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'retained_earnings'))->first(['id']);
            if (!$account) $this->accountingError('Retained Earnings Account required!');
            $dr_data = array_replace($tr_data, [
                'account_id' => $account->id, 
                'debit' => round($journal->open_balance * $currency->rate, 4),
                'fx_debit' => $journal->open_balance,
                'fx_curr_rate' => $currency->rate,
            ]);
            Transaction::create($dr_data);
        }
        $diff = Transaction::where('tid', $tr_data['tid'])->sum(\DB::raw('debit-credit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }

    /**
     * Post Bill Payment
     * @param object $bill_payment
     */
    public function post_bill_payment($bill_payment)
    {               
        $tr_category = Transactioncategory::where('code', 'pmt')->first(['id', 'code']);
        $dr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $bill_payment->supplier->ap_account_id,
            'trans_category_id' => $tr_category->id,
            'debit' => $bill_payment->amount,
            'tr_date' => $bill_payment->date,
            'due_date' => $bill_payment->date,
            'user_id' => $bill_payment->user_id,
            'note' => ($bill_payment->note ?: "{$bill_payment->payment_mode} - {$bill_payment->reference}"),
            'ins' => $bill_payment->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $bill_payment->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'payment_id' => $bill_payment->id,
            'supplier_id' => $bill_payment->supplier_id
        ];

        // Set Employee Cash Advances (Asset)
        if ($bill_payment->employee_id) {
            // debit Employee Advances Account
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'employee_advances'))->first(['id']);
            if (!$account) $this->accountingError('Employee Advances Account required!');
            $dr_data['account_id'] = $account->id;
            Transaction::create($dr_data);
            unset($dr_data['debit'], $dr_data['is_primary']);
            // credit Bank Account
            $cr_data = array_replace($dr_data, [
                'account_id' => $bill_payment->account_id,
                'credit' => $bill_payment->amount,
            ]);    
            Transaction::create($cr_data);
        }
        // Supplier Bill Payment
        else {
            if ($bill_payment->is_advance_allocation) {
                // debit Accounts Payable
                Transaction::create($dr_data);
                unset($dr_data['debit'], $dr_data['is_primary']);
                // credit Prepaid Expenses Account
                $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'prepaid_expenses'))->first(['id']);
                if (!$account) $this->accountingError('Prepaid Expenses Account required!');
                $cr_data = array_replace($dr_data, [
                    'account_id' => $account->id,
                    'credit' => $bill_payment->amount,
                ]);    
                Transaction::create($cr_data);
            } else {
                if ($bill_payment->payment_type == 'advance_payment') {
                    // credit Prepaid Expenses Account
                    $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'prepaid_expenses'))->first(['id']);
                    if (!$account) $this->accountingError('Prepaid Expenses Account required!');
                    $dr_data['account_id'] = $account->id;
                    Transaction::create($dr_data);
                    unset($dr_data['debit'], $dr_data['is_primary']);
                    // credit Bank Account
                    $cr_data = array_replace($dr_data, [
                        'account_id' => $bill_payment->account_id,
                        'credit' => $bill_payment->amount,
                    ]);    
                    Transaction::create($cr_data);
                } else {
                    // debit Payables (liability)
                    Transaction::create($dr_data);
                    unset($dr_data['debit'], $dr_data['is_primary']);
                    // credit Bank Account
                    $cr_data = array_replace($dr_data, [
                        'account_id' => $bill_payment->account_id,
                        'credit' => $bill_payment->amount,
                    ]);    
                    Transaction::create($cr_data);
                }
            }
        }
        $diff = Transaction::where('tid', $dr_data['tid'])->sum(\DB::raw('debit-credit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }

    /**
     * Direct Purchase / Bill Expense
     * 
     * @param Purchase $purchase
     */
    public function post_purchase_expense($purchase) 
    {
        // credit Accounts Payable (Creditors)
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $purchase->supplier->ap_account_id,
            'trans_category_id' => $tr_category->id,
            'credit' => $purchase->grandttl,
            'tr_date' => $purchase->date,
            'due_date' => $purchase->due_date,
            'user_id' => $purchase->user_id,
            'note' => $purchase->note,
            'ins' => $purchase->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $purchase->bill_id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'bill_id' => $purchase->bill_id,
            'supplier_id' => $purchase->supplier_id,
            'classlist_id' => $purchase->classlist_id,
        ];
        Transaction::create($cr_data);
        unset($cr_data['credit'], $cr_data['is_primary']);

        $dr_data = [];

        // debit Stock or Expense or Asset account
        // if has project, debit WIP Account
        $wip_account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'work_in_progress'))->first(['id']);
        if (!$wip_account) $this->accountingError('Work In Progress Account required!');
        $stock_account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'inventory_asset'))->first(['id']); 
        if (!$stock_account) $this->accountingError('Inventory Asset Account required');

        $asset_account_id = $stock_account->id;
        $wip_account_id = $wip_account->id;           
        foreach ($purchase->items as $item) {
            $item_subtotal = $item['qty'] * $item['rate'];
            if (!$purchase->is_tax_exc) $item_subtotal -= $item['taxrate'];

            // set Asset account and WIP account based on mapping
            if (@$item->productvariation->asset_account_id) {
                $asset_account_id = $item->productvariation->asset_account_id;
            }
            if (@$item->project->wip_account_id) {
                $projetcWip = Account::where('id', $item->project->wip_account_id)->whereHas('account_type_detail', fn($q) => $q->where('system', 'work_in_progress'))->first(['id']);
                if ($projetcWip) $wip_account_id = $projetcWip->id;
            }

            // debit Inventory
            if ($item['type'] == 'Stock') {
                if ($item['warehouse_id'] || $item['itemproject_id']) {
                    $dr_data[] = array_replace($cr_data, [
                        'account_id' => $item['warehouse_id']? $asset_account_id : ($item['itemproject_id']? $wip_account_id : null),
                        'debit' => $item_subtotal,
                        'note' => $item->description,
                        'purchase_item_id' => $item->id,
                        'project_id' => @$item->project->id,
                    ]);
                }     
            }
            // debit Expense 
            if ($item['type'] == 'Expense') {
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $item['itemproject_id']? $wip_account_id : $item['item_id'],
                    'debit' => $item_subtotal,
                    'note' => $item->description,
                    'purchase_item_id' => $item->id,
                    'project_id' => @$item->project->id,
                ]);
            }
            //  debit Asset 
            if ($item['type'] == 'Asset') {
                $asset_account = Assetequipment::find($item['item_id']);
                if (!$asset_account) $this->accountingError('Asset Account required!');
                // if project asset, use WIP account
                $dr_data[] = array_replace($cr_data, [
                    'account_id' => $item['itemproject_id']? $wip_account_id : $asset_account->id,
                    'debit' => $item_subtotal,
                    'note' => $item->description,
                    'purchase_item_id' => $item->id,
                    'project_id' => @$item->project->id,
                ]);
            }
        }

        // debit tax (VAT)
        if ($purchase['grandtax'] > 0) {
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'vat_payable'))->first(['id']);
            if (!$account) $this->accountingError('VAT Payable Account required!');
            $dr_data[] = array_replace($cr_data, [
                'account_id' => $account->id, 
                'debit' => $purchase['grandtax'],
                'purchase_item_id' => null,
                'project_id' => null,
            ]);
        }
        Transaction::insert($dr_data); 
        $diff = Transaction::where('tid', $cr_data['tid'])->sum(\DB::raw('credit-debit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }

    /**
     * Goods Received Note Bill
     * 
     * @param UtilityBill $purchase
     */
    public function post_grn_bill($utility_bill)
    {
        // debit Goods Received Not Invoiced Account (Liability)
        $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'goods_received_not_invoiced'))->first(['id']);
        if (!$account) $this->accountingError('Goods Received Not Invoiced (GRNI) Account required!');
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $dr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $utility_bill->fx_curr_rate > 0? $utility_bill->fx_subtotal : $utility_bill->subtotal,
            'fx_debit' => $utility_bill->fx_curr_rate > 0? $utility_bill->subtotal : 0,
            'tr_date' => $utility_bill->date,
            'due_date' => $utility_bill->due_date,
            'user_id' => $utility_bill->user_id,
            'note' => $utility_bill->note,
            'ins' => $utility_bill->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $utility_bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'supplier_id' => $utility_bill->supplier_id,
            'bill_id' => $utility_bill->id,
            'fx_curr_rate' => $utility_bill->fx_curr_rate,
        ];
        Transaction::create($dr_data);
        unset($dr_data['debit'], $dr_data['fx_debit'], $dr_data['is_primary']);

        // debit VAT Payable
        if ($utility_bill->tax > 0) {
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'vat_payable'))->first(['id']);
            if (!$account) $this->accountingError('VAT Payable Account required!');    
            $cr_data = array_replace($dr_data, [
                'account_id' => $account->id,
                'debit' => $utility_bill->fx_curr_rate > 0? $utility_bill->fx_tax : $utility_bill->tax,
                'fx_debit' => $utility_bill->fx_curr_rate > 0? $utility_bill->tax : 0,
            ]);
            Transaction::create($cr_data);
        }

        // credit Accounts Payable
        $cr_data = array_replace($dr_data, [
            'account_id' => $utility_bill->supplier->ap_account_id,
            'credit' => $utility_bill->fx_curr_rate > 0? $utility_bill->fx_total : $utility_bill->total,
            'fx_debit' => $utility_bill->fx_curr_rate > 0? $utility_bill->total : 0,
        ]);    
        Transaction::create($cr_data);

        $diff = Transaction::where('tid', $dr_data['tid'])->sum(\DB::raw('credit-debit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }  

    /**
     * Casual Wage Bill
     * 
     * @param UtilityBill $purchase
     */
    public function post_casual_wage_bill($bill)
    {
        // debit WIP
        $tr_category = Transactioncategory::where('code', 'salaries')->first(['id', 'code']);
        $wipAccount = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'work_in_progress'))->first(['id']);
        if (!$wipAccount) $this->accountingError('Work In Progress Account required!');

        $dr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $wipAccount->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $bill->total,
            'tr_date' => $bill->date,
            'due_date' => $bill->due_date,
            'user_id' => $bill->user_id,
            'note' => $bill->note,
            'ins' => $bill->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'supplier_id' => $bill->supplier_id,
            'bill_id' => $bill->id,
            'project_id' => $bill->project_id,
        ];
        Transaction::create($dr_data);
        unset($dr_data['debit'], $dr_data['is_primary']);

        // credit Accounts Payable
        $apExists = Account::where('id', $bill->supplier->ap_account_id)->exists();
        if (!$apExists) $this->accountingError('Mapped Supplier - Accounts Payable Account required!');
        $cr_data = array_replace($dr_data, [
            'account_id' => $bill->supplier->ap_account_id,
            'credit' => $bill->total,
        ]);    
        Transaction::create($cr_data);

        $diff = Transaction::where('tid', $dr_data['tid'])->sum(\DB::raw('credit-debit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }  

    /**
     * Manual General Journal
     * @param Journal $journal
     */
    public function post_journal_entry($journal)
    {   
        $tr_category = Transactioncategory::where('code', 'genjr')->first(['id', 'code']);
        $data = [
            'tid' => Transaction::max('tid')+1,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $journal->date,
            'due_date' => $journal->date,
            'user_id' => $journal->user_id,
            'ins' => $journal->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $journal->id,
            'user_type' => 'company',
            'is_primary' => 0,
            'note' => $journal->note,
            'man_journal_id' => $journal->id,
        ];

        $tr_data = [];
        foreach ($journal->items as $i => $item) {
            if ($item->debit > 0) {
                $tr_data[] = array_replace($data, [
                    'account_id' => $item->account_id,
                    'debit' => $item->debit,
                    'credit' => 0,
                    'supplier_id' => $item->supplier_id,
                    'customer_id' => $item->customer_id,
                    'project_id' => $item->project_id,
                ]);
            } elseif ($item->credit > 0) {
                $tr_data[] = array_replace($data, [
                    'account_id' => $item->account_id,
                    'credit' => $item->credit,
                    'debit' => 0,
                    'supplier_id' => $item->supplier_id,
                    'customer_id' => $item->customer_id,
                    'project_id' => $item->project_id,
                ]);
            }
            if ($i == count($tr_data)-1) {
                $tr_data[0]['is_primary'] = 1;
            }
        }
        Transaction::insert($tr_data);  
          
        $diff = Transaction::where('tid', $data['tid'])->sum(\DB::raw('credit-debit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }

    /**
     * Account Charges
     * @param Charge $charge
     */
    public function post_account_charge($charge)
    {
        // credit Bank Account
        $tr_category = Transactioncategory::where('code', 'chrg')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $charge->bank_id,
            'trans_category_id' => $tr_category->id,
            'credit' => $charge->amount,
            'tr_date' => $charge->date,
            'due_date' => $charge->date,
            'user_id' => $charge->user_id,
            'ins' => $charge->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $charge->id,
            'user_type' => 'customer',
            'is_primary' => 1,
            'note' => $charge->note,
            'charge_id' => $charge->id
        ];
        Transaction::create($cr_data);
        unset($cr_data['credit'], $cr_data['is_primary']);

        // debit Expense Account
        $dr_data = array_replace($cr_data, [
            'account_id' => $charge['expense_id'],
            'debit' => $charge['amount'],
        ]);
        Transaction::create($dr_data);
    }

    /**
     * Bank Transfer
     * @param Bank $bank
     */
    public function post_bank_transfer($bank_xfer)
    {
        $tr_category = Transactioncategory::where('code', 'xfer')->first(['id', 'code']);
        $tr_data = [
            'tid' => Transaction::max('tid')+1,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $bank_xfer->transaction_date,
            'due_date' => $bank_xfer->transaction_date,
            'user_id' => $bank_xfer->user_id,
            'note' => $bank_xfer->note,
            'ins' => $bank_xfer->ins,
            'tr_type' => $tr_category->code,
            'is_primary' => 1,
            'user_type' => 'employee',
            'bank_transfer_id' => $bank_xfer->id,
        ];

        $sourceCurrency = @$bank_xfer->source_account->currency;
        $destCurrency = @$bank_xfer->dest_account->currency;
        if (!$sourceCurrency || !$destCurrency) $this->accountingError('Currency required for transfer accounts');

        $sourceAccount = $bank_xfer->source_account;
        $destAccount = $bank_xfer->dest_account;
        if ($sourceCurrency->rate == 1 && $destCurrency->rate == 1) {
            // debit dest account
            $dr_data = array_replace($tr_data, [
                'account_id' => $destAccount->id,
                'debit' => $bank_xfer->amount,
                'is_primary' => 1,
            ]);
            Transaction::create($dr_data);
            unset($tr_data['is_primary']);
    
            // credit source account
            $cr_data = array_replace($tr_data, [
                'account_id' => $sourceAccount->id,
                'credit' => $bank_xfer->amount,
            ]);
            Transaction::create($cr_data);
        } elseif ($sourceCurrency->rate == 1) {
            // debit dest account
            $dr_data = array_replace($tr_data, [
                'account_id' => $destAccount->id,
                'debit' =>  round($bank_xfer->dest_amount_fx / $bank_xfer->default_rate, 4),
                'fx_debit' => $bank_xfer->dest_amount_fx,
                'fx_curr_rate' => $bank_xfer->default_rate,
                'is_primary' => 1,
            ]);
            Transaction::create($dr_data);
            unset($tr_data['is_primary']);
    
            // credit source account
            $cr_data = array_replace($tr_data, [
                'account_id' => $sourceAccount->id,
                'credit' =>  round($bank_xfer->source_amount_fx / $bank_xfer->default_rate, 4),
                'fx_credit' => $bank_xfer->source_amount_fx,
                'fx_curr_rate' => $bank_xfer->default_rate,
            ]);
            Transaction::create($cr_data);

            /** Foreign Gain/Loss Adjustment */
            if ($bank_xfer->fx_gain_total > 0) {
                // credit Foreign Gain
                $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'foreign_currency_gain'))->first(['id']);
                if (!$account) $this->accountingError('Foreign Currency Gain Account required!');
                $cr_data = array_replace($tr_data, [
                    'account_id' => $account->id,
                    'credit' =>  round($bank_xfer->fx_gain_total / $bank_xfer->default_rate, 4),
                    'fx_credit' => $bank_xfer->fx_gain_total,
                    'fx_curr_rate' => $bank_xfer->default_rate,
                ]);    
                Transaction::create($cr_data);
            } elseif ($bank_xfer->fx_loss_total > 0) {
                // debit Foreign Loss
                $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'foreign_currency_loss'))->first(['id']);
                if (!$account) $this->accountingError('Foreign Currency Loss Account required!');
                $dr_data = array_replace($tr_data, [
                    'account_id' => $account->id,
                    'debit' =>  round($bank_xfer->fx_loss_total / $bank_xfer->default_rate, 4),
                    'fx_debit' => $bank_xfer->fx_loss_total,
                    'fx_curr_rate' => $bank_xfer->default_rate,
                ]);    
                Transaction::create($dr_data);
            }
        } elseif ($destCurrency->rate == 1) {
            // debit dest account
            $dr_data = array_replace($tr_data, [
                'account_id' => $destAccount->id,
                'debit' => $bank_xfer->dest_amount_fx,
                'fx_debit' =>  round($bank_xfer->dest_amount_fx / $bank_xfer->default_rate, 4),
                'fx_curr_rate' => $bank_xfer->default_rate,
                'is_primary' => 1,
            ]);
            Transaction::create($dr_data);
            unset($tr_data['is_primary']);
    
            // credit source account
            $cr_data = array_replace($tr_data, [
                'account_id' => $sourceAccount->id,
                'credit' => $bank_xfer->source_amount_fx,
                'fx_credit' =>  round($bank_xfer->source_amount_fx / $bank_xfer->default_rate, 4),
                'fx_curr_rate' => $bank_xfer->default_rate,
            ]);
            Transaction::create($cr_data);

            /** Foreign Gain/Loss Adjustment */
            if ($bank_xfer->fx_gain_total > 0) {
                // credit Foreign Gain
                $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'foreign_currency_gain'))->first(['id']);
                if (!$account) $this->accountingError('Foreign Currency Gain Account required!');
                $cr_data = array_replace($tr_data, [
                    'account_id' => $account->id,
                    'credit' => $bank_xfer->fx_gain_total,
                    'fx_credit' =>  round($bank_xfer->fx_gain_total / $bank_xfer->default_rate, 4),
                    'fx_curr_rate' => $bank_xfer->default_rate,
                ]);    
                Transaction::create($cr_data);
            } elseif ($bank_xfer->fx_loss_total > 0) {
                // debit Foreign Loss
                $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'foreign_currency_loss'))->first(['id']);
                if (!$account) $this->accountingError('Foreign Currency Loss Account required!');
                $dr_data = array_replace($tr_data, [
                    'account_id' => $account->id,
                    'debit' => $bank_xfer->fx_loss_total,
                    'fx_debit' =>  round($bank_xfer->fx_loss_total / $bank_xfer->default_rate, 4),
                    'fx_curr_rate' => $bank_xfer->default_rate,
                ]);    
                Transaction::create($dr_data);
            }
        } else  {
            // debit dest account
            $dr_data = array_replace($tr_data, [
                'account_id' => $destAccount->id,
                'debit' => $bank_xfer->dest_amount_fx,
                'fx_debit' =>  round($bank_xfer->dest_amount_fx / $bank_xfer->default_rate, 4),
                'fx_curr_rate' => $bank_xfer->default_rate,
                'is_primary' => 1,
            ]);
            Transaction::create($dr_data);
            unset($tr_data['is_primary']);
    
            // credit source account
            $cr_data = array_replace($tr_data, [
                'account_id' => $sourceAccount->id,
                'credit' => $bank_xfer->source_amount_fx,
                'fx_credit' =>  round($bank_xfer->source_amount_fx / $bank_xfer->default_rate, 4),
                'fx_curr_rate' => $bank_xfer->default_rate,
            ]);
            Transaction::create($cr_data);

            if (round($sourceCurrency->rate) == round($destCurrency->rate)) {
                /** Foreign Gain/Loss Adjustment */
                if ($bank_xfer->fx_gain_total > 0) {
                    // credit Foreign Gain
                    $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'foreign_currency_gain'))->first(['id']);
                    if (!$account) $this->accountingError('Foreign Currency Gain Account required!');
                    $cr_data = array_replace($tr_data, [
                        'account_id' => $account->id,
                        'credit' => $bank_xfer->fx_gain_total,
                        'fx_credit' => round($bank_xfer->fx_gain_total / $bank_xfer->default_rate, 4),
                        'fx_curr_rate' => $bank_xfer->default_rate,
                    ]);    
                    Transaction::create($cr_data);
                } elseif ($bank_xfer->fx_loss_total > 0) {
                    // debit Foreign Loss
                    $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'foreign_currency_loss'))->first(['id']);
                    if (!$account) $this->accountingError('Foreign Currency Loss Account required!');
                    $dr_data = array_replace($tr_data, [
                        'account_id' => $account->id,
                        'debit' => $bank_xfer->fx_loss_total,
                        'fx_debit' => round($bank_xfer->fx_gain_total / $bank_xfer->default_rate, 4),
                        'fx_curr_rate' => $bank_xfer->default_rate,
                    ]);    
                    Transaction::create($dr_data);
                }
            } else {
                /** Foreign Gain/Loss Adjustment */
                if ($bank_xfer->fx_gain_total > 0) {
                    // credit Foreign Gain
                    $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'foreign_currency_gain'))->first(['id']);
                    if (!$account) $this->accountingError('Foreign Currency Gain Account required!');
                    $cr_data = array_replace($tr_data, [
                        'account_id' => $account->id,
                        'credit' => $bank_xfer->fx_gain_total,
                    ]);    
                    Transaction::create($cr_data);
                } elseif ($bank_xfer->fx_loss_total > 0) {
                    // debit Foreign Loss
                    $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'foreign_currency_loss'))->first(['id']);
                    if (!$account) $this->accountingError('Foreign Currency Loss Account required!');
                    $dr_data = array_replace($tr_data, [
                        'account_id' => $account->id,
                        'debit' => $bank_xfer->fx_loss_total,
                    ]);    
                    Transaction::create($dr_data);
                }
            }
        }
        $diff = Transaction::where('tid', $tr_data['tid'])->sum(\DB::raw('debit-credit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }

    /**
     * Goods Received Without Invoice
     * 
     * @param GoodsReceivedNote $grn
     */
    public function post_uninvoiced_grn($grn)
    {
        // credit Goods Received Not Invoiced (liability)
        $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'goods_received_not_invoiced'))->first(['id']);
        if (!$account) $this->accountingError('Goods Received Not Invoiced (GRNI) Account required!');
        $tr_category = Transactioncategory::where('code', 'grn')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'credit' => $grn->fx_curr_rate > 1? $grn->fx_subtotal : $grn->subtotal,
            'fx_credit' => $grn->fx_curr_rate > 1? $grn->subtotal : 0,
            'tr_date' => $grn->date,
            'due_date' => $grn->date,
            'user_id' => $grn->user_id,
            'note' => $grn->note,
            'ins' => $grn->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $grn->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'supplier_id' => $grn->supplier_id,
            'grn_id' => $grn->id,
            'fx_curr_rate' => $grn->fx_curr_rate,
        ];
        Transaction::create($cr_data);
        unset($cr_data['credit'], $cr_data['fx_credit'], $cr_data['is_primary']);

        // debit WIP Account (Asset)
        $project_items = $grn->items()->whereHas('project')->with('project')->get();
        if ($project_items->count()) {
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'work_in_progress'))->first(['id']);
            if (!$account) $this->accountingError('Work In Progress (WIP) Account required!');
            foreach ($project_items as $item) {
                $projectWip = Account::where('id', $item->project->wip_account_id)->whereHas('account_type_detail', fn($q) => $q->where('system', 'work_in_progress'))->first(['id']);
                $wip_account_id = @$projectWip->id ?: $account->id;
                $note = $item->purchaseorder_item->productvariation->name;
                $subtotal = $item->qty * $item->rate;
                $fx_subtotal = $item->fx_subtotal;
                if ($subtotal > 0) {
                    $dr_data = array_replace($cr_data, [
                        'account_id' => $wip_account_id,
                        'debit' => $grn->fx_curr_rate > 1? $fx_subtotal : $subtotal,
                        'fx_debit' => $grn->fx_curr_rate > 1? $subtotal : 0,
                        'note' => $note,
                        'grn_item_id' => $item->id,
                        'project_id' => $item->project->id,
                    ]);    
                    Transaction::create($dr_data);
                }
            }
        }

        // debit Inventory Account (Asset)
        $stock_items = $grn->items()->whereHas('warehouse')->get();
        if ($stock_items->count()) {
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'inventory_asset'))->first(['id']);
            if (!$account) $this->accountingError('Inventory Asset Account required!');
            foreach ($stock_items as $item) {
                $assetAccount = Account::where('id', @$item->purchaseorder_item->productvariation->asset_account_id)
                    ->whereHas('account_type_detail', fn($q) => $q->where('system', 'inventory_asset'))
                    ->first(['id']);

                $asset_account_id = @$assetAccount->id ?: $account->id;
                $note = $item->purchaseorder_item->productvariation->name;
                $subtotal = $item->qty * $item->rate;
                $fx_subtotal = $item->fx_subtotal;
                if ($subtotal > 0) {
                    $dr_data = array_replace($cr_data, [
                        'account_id' => $asset_account_id,
                        'debit' => $grn->fx_curr_rate > 1? $fx_subtotal : $subtotal,
                        'fx_debit' => $grn->fx_curr_rate > 1? $subtotal : 0,
                        'note' => $note,
                    ]);    
                    Transaction::create($dr_data);
                }
            }
        }

        // debit Expense/Asset Account
        $service_items = $grn->items()->whereHas('account')->get();
        foreach ($service_items as $item) {
            $note = $item->purchaseorder_item->productvariation->name;
            $subtotal = $item->qty * $item->rate;
            $fx_subtotal = $item->fx_subtotal;
            if ($subtotal > 0) {
                $dr_data = array_replace($cr_data, [
                    'account_id' => $item->account_id,
                    'debit' => $grn->fx_curr_rate > 1? $fx_subtotal : $subtotal,
                    'fx_debit' => $grn->fx_curr_rate > 1? $subtotal : 0,
                    'note' => $note,
                ]);    
                Transaction::create($dr_data);
            }
        }

        $diff = Transaction::where('tid', $cr_data['tid'])->sum(\DB::raw('debit-credit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }

    /**
     * Goods Received With Invoice
     * 
     * @param UtilityBill $utility_bill
     */
    public function post_invoiced_grn_bill($utility_bill)
    {
        // credit Accounts Payable (Liability)
        $tr_category = Transactioncategory::where('code', 'bill')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $utility_bill->supplier->ap_account_id,
            'trans_category_id' => $tr_category->id,
            'tr_date' => $utility_bill->date,
            'due_date' => $utility_bill->due_date,
            'user_id' => $utility_bill->user_id,
            'note' => $utility_bill->note,
            'ins' => $utility_bill->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $utility_bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'supplier_id' => $utility_bill->supplier_id,
            'bill_id' => $utility_bill->id,
            'fx_curr_rate' => $utility_bill->fx_curr_rate,
            'credit' => $utility_bill->fx_curr_rate > 1? $utility_bill->fx_total : $utility_bill->total,
            'fx_credit' => $utility_bill->fx_curr_rate > 1? $utility_bill->total : 0,
        ];
        Transaction::create($cr_data);
        unset($cr_data['is_primary'], $cr_data['credit'], $cr_data['fx_credit']);

        // debit WIP Account (Asset)
        $project_items = $utility_bill->grn_items()->whereHas('project')->with('project')->get();
        if ($project_items->count()) {
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'work_in_progress'))->first(['id']);
            if (!$account) $this->accountingError('Work In Progress (WIP) Account required!');
            foreach ($project_items as $item) {
                $projectWip = Account::where('id', $item->project->wip_account_id)->whereHas('account_type_detail', fn($q) => $q->where('system', 'work_in_progress'))->first(['id']);
                $wip_account_id = @$projectWip->id ?: $account->id;

                $note = $item->purchaseorder_item->productvariation->name;
                $subtotal = $item->qty * $item->rate;
                $fx_subtotal = $item->fx_subtotal;
                $dr_data = array_replace($cr_data, [
                    'account_id' => $wip_account_id,
                    'debit' => $utility_bill->fx_curr_rate > 1? $fx_subtotal : $subtotal,
                    'fx_debit' => $utility_bill->fx_curr_rate > 1? $subtotal : 0,
                    'note' => $note,
                    'grn_item_id' => $item->id,
                    'project_id' => $item->project->id,
                ]);    
                Transaction::create($dr_data);
            }
        }

        // debit Inventory Account (Asset)
        $stock_items = $utility_bill->grn_items()->whereHas('warehouse')->get();
        if ($stock_items->count()) {
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'inventory_asset'))->first(['id']);
            if (!$account) $this->accountingError('Inventory Asset Account required!');
            foreach ($stock_items as $item) {
                $invAccount = Account::where('id', $item->purchaseorder_item->productvariation->asset_account_id)
                ->whereHas('account_type_detail', fn($q) => $q->where('system', 'inventory_asset'))
                ->first(['id']);

                $note = $item->purchaseorder_item->productvariation->name;
                $subtotal = $item->qty * $item->rate;
                $fx_subtotal = $item->fx_subtotal;
                $dr_data = array_replace($cr_data, [
                    'account_id' => @$invAccount->id ?: $account->id,
                    'debit' => $utility_bill->fx_curr_rate > 1? $fx_subtotal : $subtotal,
                    'fx_debit' => $utility_bill->fx_curr_rate > 1? $subtotal : 0,
                    'note' => $note,
                ]);    
                Transaction::create($dr_data);
            }
        }

        // debit Expense/Asset Account
        $service_items = $utility_bill->grn_items()->whereHas('account')->get();
        foreach ($service_items as $item) {
            $note = $item->purchaseorder_item->productvariation->name;
            $subtotal = $item->qty * $item->rate;
            $fx_subtotal = $item->fx_subtotal;
            if ($subtotal > 0) {
                $dr_data = array_replace($cr_data, [
                    'account_id' => $item->account_id,
                    'debit' => $utility_bill->fx_curr_rate > 1? $fx_subtotal : $subtotal,
                    'fx_debit' => $utility_bill->fx_curr_rate > 1? $subtotal : 0,
                    'note' => $note,
                ]);    
                Transaction::create($dr_data);
            }
        }

        // debit VAT Payable
        if ($utility_bill->tax > 0) {
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'vat_payable'))->first(['id']);
            if (!$account) $this->accountingError('VAT Payable Account required!');
            $dr_data = array_replace($cr_data, [
                'account_id' => $account->id,
                'debit' => $utility_bill->fx_curr_rate > 1? $utility_bill->fx_tax : $utility_bill->tax,
                'fx_debit' => $utility_bill->fx_curr_rate > 1? $utility_bill->tax : 0,
            ]);
            Transaction::create($dr_data);
        }
        
        $diff = Transaction::where('tid', $cr_data['tid'])->sum(\DB::raw('debit-credit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }

    /**
     * Payroll
     * 
     * @param Payroll $payroll
     */
    public function post_payroll($bill, $bill_type)
    {
        // credit Accounts Payable (Liability)
        $tr_category = Transactioncategory::where('code', 'salaries')->first(['id', 'code']);
        $cr_data = [
            'tid' => Transaction::max('tid')+1,
            'account_id' => $bill->supplier->ap_account_id,
            'trans_category_id' => $tr_category->id,
            'credit' => $bill->total,
            'tr_date' => $bill->date,
            'due_date' => $bill->due_date,
            'user_id' => $bill->user_id,
            'note' => $bill->note,
            'ins' => $bill->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $bill->id,
            'user_type' => 'supplier',
            'is_primary' => 1,
            'supplier_id' => $bill->supplier_id,
            'bill_id' => $bill->id,
        ];            
        Transaction::create($cr_data);
        unset($cr_data['credit'], $cr_data['is_primary']);

        // debit Salary Expense
        if (in_array($bill_type, ['net_pay', 'paye', 'nssf', 'nhif', 'shif', 'ahl', 'other_deductions', 'advances'])) {
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'payroll_salary_expenses'))->first(['id']);
            if (!$account) $this->accountingError('Payroll Salary Expenses Account required');
            $dr_data = array_replace($cr_data, [
                'account_id' => $account->id,
                'debit' => $bill->total,
                'note' => $bill->items->first()->note,
            ]);    
            Transaction::create($dr_data);

            // Adjust Accounts Payable
            if ($bill_type == 'advances') {
                // debit Contra Payroll Liabilities (Liability)
                $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'contra_payroll_liability'))->first(['id']);
                if (!$account) $this->accountingError('Contra Payroll Liability Account required');
                $dr_data = array_replace($cr_data, [
                    'account_id' => $account->id,
                    'debit' => $bill->total,
                    'note' => $bill->items->first()->note,
                ]);    
                Transaction::create($dr_data);

                // credit Employee Advances (Asset)
                $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'employee_advances'))->first(['id']);
                if (!$account) $this->accountingError('Employee Advances Account required');
                $cr_data = array_replace($cr_data, [
                    'account_id' => $account->id,
                    'credit' => $bill->total,
                    'note' => $bill->items->first()->note,
                ]);    
                Transaction::create($cr_data);
            }
        } elseif (in_array($bill_type, ['employer_nssf', 'employer_nhif', 'employer_shif', 'employer_nita', 'employer_helb'])) {
            // debit Payroll Tax Expense (Liability)
            $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'payroll_tax_expenses'))->first(['id']);
            if (!$account) $this->accountingError('Payroll Tax Expenses Account required');
            $dr_data = array_replace($cr_data, [
                'account_id' => $account->id,
                'debit' => $bill->total,
                'note' => $bill->items->first()->note,
            ]);    
            Transaction::create($dr_data);
        } 
        $diff = Transaction::where('tid', $cr_data['tid'])->sum(\DB::raw('debit-credit'));
        if (round($diff)) $this->accountingError('Accounting error: Total Debits do not equal to Total Credits');
    }
}