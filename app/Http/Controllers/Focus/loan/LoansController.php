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

use App\Repositories\Focus\loan\LoanRepository;
use App\Http\Responses\ViewResponse;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\lender\Lender;
use App\Models\loan\Loan;
use App\Models\loan\Paidloan;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use Illuminate\Http\Request;

/**
 * CustomersController
 */
class LoansController extends Controller
{
    /**
     * variable to store the repository object
     * @var LoanRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param LoanRepository $repository ;
     */
    public function __construct(LoanRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\customer\ManageCustomerRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.loans.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateCustomerRequestNamespace $request
     * @return \App\Http\Responses\Focus\customer\CreateResponse
     */
    public function create()
    {
        $tid = Loan::where('ins', auth()->user()->ins)->max('tid');
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get(['id', 'holder', 'account_type'])->pluck('holder', 'id');
        $lenders = Lender::get(['id', 'name'])->pluck('name', 'id');
        
        $employees = Hrm::where('id', auth()->user()->id)->get(['id', 'first_name', 'last_name']);
        // manage permission
        if (false) $employees = User::get(['id', 'first_name', 'last_name']);

        return new ViewResponse('focus.loans.create', compact('tid', 'accounts', 'lenders', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCustomerRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Loan', $th);
        }

        return new RedirectResponse(route('biller.loans.index'), ['flash_success' => 'Loan Created Successfully']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, Loan $loan)
    {
        try {
            $this->repository->update($loan, $request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Loan', $th);
        }

        return new RedirectResponse(route('biller.loans.index'), ['flash_success' => 'Loan Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Loan $loan)
    {
        return new ViewResponse('focus.loans.view', compact('loan'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param App\Models\Loan $loan
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Loan $loan)
    {
        try {
            $this->repository->delete($loan);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Loan', $th);
        }

        return new RedirectResponse(route('biller.loans.index'), ['flash_success' => 'Loan Deleted Successfully']);
    }

    /**
     * Form for paying loan
     */
    public function pay_loans()
    {
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->whereIn('system', ['bank', 'expense', 'other_current_liability']);
        })->where('system', null)
            ->get(['id', 'holder', 'account_type']);
        $last_tid = Paidloan::where('ins', auth()->user()->ins)->max('tid');

        return new ViewResponse('focus.loans.pay_loans', compact('last_tid', 'accounts'));
    }

    /**
     * Persist paid loan in storage
     */
    public function store_loans(Request $request)
    {
        // extract input fields
        $data = $request->only([
            'lender_id', 'bank_id', 'tid', 'date', 'payment_mode', 'amount', 'ref', 'interest_id', 'penalty_id'
        ]);
        $data_items = $request->only(['loan_id', 'paid', 'interest', 'penalty']);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        // modify and filter paid bill
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($item) {
            return $item['paid'];
        });

        try {
            $result = $this->repository->store_loans(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Loans Payment', $th);
        }

        return new RedirectResponse(route('biller.loans.index'), ['flash_success' => 'Loans payment successfully received']);
    }

    /**
     * Lenders for select dropdown search 
     */
    public function lenders()
    {
        // loan lender accounts
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'loan');
        })->where('holder', 'LIKE', '%' . request('keyword') . '%')
            ->where('system', null)
            ->limit(6)->get(['id', 'holder', 'account_type']);

        return response()->json($accounts);
    }

    /**
     * Lender loans
     */
    public function lender_loans()
    {
        $accounts = Loan::where(['lender_id' => request('id'), 'is_approved' => 1])
            ->whereIn('status', ['pending', 'partial'])->get();

        return response()->json($accounts);
    }
    // Loan transacton
    public function post_transaction_loan($result)
    {
        // debit Accounts Receivable (Debtors)
        $account = Account::where('system', 'receivable')->first(['id']);
        $tr_category = Transactioncategory::where('code', 'inv')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $dr_data = [
            'tid' => $tid,
            'account_id' => $account->id,
            'trans_category_id' => $tr_category->id,
            'debit' => $result->total,
            'tr_date' => $result->invoicedate,
            'due_date' => $result->invoiceduedate,
            'user_id' => $result->user_id,
            'note' => $result->notes,
            'ins' => $result->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $result->id,
            'user_type' => 'customer',
            'is_primary' => 1,
        ];
        Transaction::create($dr_data);

        // credit Customer Income (intermediary ledger account)
        // $account = Account::where('system', 'client_income')->first(['id']);
        // unset($dr_data['debit'], $dr_data['is_primary']);
        // $inc_cr_data = array_replace($dr_data, [
        //     'account_id' => $account->id,
        //     'credit' => $result->subtotal,
        // ]);

        // credit Revenue Account (Income)
        unset($dr_data['debit'], $dr_data['is_primary']);
        $inc_cr_data = array_replace($dr_data, [
            'account_id' => $result->account_id,
            'credit' => $result->subtotal,
        ]);

        // credit tax (VAT)
        $account = Account::where('system', 'tax')->first(['id']);
        $tax_cr_data = array_replace($dr_data, [
            'account_id' => $account->id,
            'credit' => $result->tax,
        ]);
        Transaction::insert([$inc_cr_data, $tax_cr_data]);

        // WIP and COG Accounts
        $tr_data = [];
        // invoice related quotes and pi query
        $q = Quote::whereIn('id', function ($q) use ($result) {
            $q->select('quote_id')->from('invoice_items')->where('invoice_id', $result->id);
        });
        // update query results
        $q1 = clone $q;
        $q1->update(['closed_by' => $result['user_id']]);
        // fetch query results
        $quotes = $q->get();

        // stock amount of items issued from inventory
        $store_inventory_amount = 0;

        // direct purchase item amounts of items issued directly to project
        $dirpurch_inventory_amount = 0;
        $dirpurch_expense_amount = 0;
        $dirpurch_asset_amount = 0;
        foreach ($quotes as $quote) {
            $store_inventory_amount  = $quote->projectstock->sum('subtotal');

            // direct purchase items issued to project
            if (isset($quote->project_quote->project)) {
                foreach ($quote->project_quote->project->purchase_items as $item) {
                    if ($item->itemproject_id) {
                        $subtotal = $item->amount - $item->taxrate;
                        if ($item->type == 'Expense') $dirpurch_expense_amount += $subtotal;
                        elseif ($item->type == 'Stock') $dirpurch_inventory_amount += $subtotal;
                        elseif ($item->type == 'Asset') $dirpurch_asset_amount += $subtotal;
                    }
                }
            }
        }

        // credit WIP account and debit COG
        $wip_account = Account::where('system', 'wip')->first(['id']);
        $cog_account = Account::where('system', 'cog')->first(['id']);
        $cr_data = array_replace($dr_data, ['account_id' => $wip_account->id, 'is_primary' => 1]);
        $dr_data = array_replace($dr_data, ['account_id' => $cog_account->id, 'is_primary' => 0]);
        if ($dirpurch_inventory_amount > 0) {
            $tr_data[] = array_replace($cr_data, ['credit' => $dirpurch_inventory_amount]);
            $tr_data[] = array_replace($dr_data, ['debit' => $dirpurch_inventory_amount]);
        }
        if ($dirpurch_expense_amount > 0) {
            $tr_data[] = array_replace($cr_data, ['credit' => $dirpurch_expense_amount]);
            $tr_data[] = array_replace($dr_data, ['debit' => $dirpurch_expense_amount]);
        }
        if ($dirpurch_asset_amount > 0) {
            $tr_data[] = array_replace($cr_data, ['credit' => $dirpurch_asset_amount]);
            $tr_data[] = array_replace($dr_data, ['debit' => $dirpurch_asset_amount]);
        }
        if ($store_inventory_amount > 0) {
            $tr_data[] = array_replace($cr_data, ['credit' => $store_inventory_amount]);
            $tr_data[] = array_replace($dr_data, ['debit' => $store_inventory_amount]);
        }

        $tr_data = array_map(function ($v) {
            if (isset($v['debit']) && $v['debit'] > 0) $v['credit'] = 0;
            elseif (isset($v['credit']) && $v['credit'] > 0) $v['debit'] = 0;
            return $v;
        }, $tr_data);
        Transaction::insert($tr_data);
        aggregate_account_transactions();
    }
}
