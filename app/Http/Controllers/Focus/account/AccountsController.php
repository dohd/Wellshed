<?php

namespace App\Http\Controllers\Focus\account;

use App\Models\account\Account;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\account\CreateResponse;
use App\Http\Responses\Focus\account\EditResponse;
use App\Repositories\Focus\account\AccountRepository;
use App\Http\Requests\Focus\account\ManageAccountRequest;
use App\Http\Requests\Focus\account\StoreAccountRequest;
use App\Models\account\AccountType;
use App\Models\account\AccountTypeDetail;
use App\Models\classlist\Classlist;
use App\Models\customer\Customer;
use App\Models\transaction\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

/**
 * AccountsController
 */
class AccountsController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $repository ;
     */
    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\account\ManageAccountRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageAccountRequest $request)
    {
        $accountTypes = AccountType::orderBy('category', 'asc')->get(['id', 'name', 'category']);

        return new ViewResponse('focus.accounts.index', compact('accountTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateAccountRequestNamespace $request
     * @return \App\Http\Responses\Focus\account\CreateResponse
     */
    public function create(StoreAccountRequest $request)
    {
        return new CreateResponse('focus.accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAccountRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreAccountRequest $request)
    {
        $request->validate([
            'number' => 'required',
            'holder' => 'required',
            'parent_id' => 'required_if:is_sub_account,=,1',
            'account_type' => 'required',
        ]);
        $input = $request->except(['_token']);

        try {
            $this->repository->create($input);
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Account', $th);
        }

        return new RedirectResponse(route('biller.accounts.index'), ['flash_success' => trans('alerts.backend.accounts.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\account\Account $account
     * @param EditAccountRequestNamespace $request
     * @return \App\Http\Responses\Focus\account\EditResponse
     */
    public function edit(Account $account)
    {
        return new EditResponse($account);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAccountRequestNamespace $request
     * @param App\Models\account\Account $account
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreAccountRequest $request, Account $account)
    {
        $request->validate([
            'number' => 'required',
            'holder' => 'required',
            'parent_id' => 'required_if:is_sub_account,=,1',
            'account_type' => 'required',
        ]);
        $input = $request->except(['_token', 'ins']);

        try {
            $this->repository->update($account, $input);
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Account', $th);
        }

        return new RedirectResponse(route('biller.accounts.index'), ['flash_success' => trans('alerts.backend.accounts.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAccountRequestNamespace $request
     * @param App\Models\account\Account $account
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Account $account)
    {
        try {
            $this->repository->delete($account);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Account', $th);
        }

        return new RedirectResponse(route('biller.accounts.index'), ['flash_success' => trans('alerts.backend.accounts.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAccountRequestNamespace $request
     * @param App\Models\account\Account $account
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Account $account)
    {
        // default params
        $params =  [
            'rel_type' => 9, 
            'rel_id' => $account->id, 
        ];
        $is_customer = $account->transactions()->whereNotNull('customer_id')->exists();
        $is_supplier = $account->transactions()->whereNotNull('supplier_id')->exists();
        if ($is_customer) $params['system'] = 'receivable';
        if ($is_supplier) $params['system'] = 'payable';
        if (in_array(@$account->account_type_detail->system, ['checking', 'savings', 'money_market'])) $params['system'] = 'bank';
        if (@$account->account_type_detail->system == 'work_in_progress') $params['system'] = 'wip';

        return new RedirectResponse(route('biller.transactions.index', $params), []);
    }

    /**
     * Seach Parent Account
     * 
     */
    public function search_parent_account(Request $request) 
    {
        $t = request('term');
        $accounts = Account::whereNull('parent_id')
            ->where('account_type', request('account_type'))
            ->where(fn($q) => $q->where('holder', 'LIKE', '%' . $t . '%')->orWhere('number', 'LIKE', '%' . $t . '%'))
            ->get();
        return response()->json($accounts);
    }

    /**
     * Seach Detail Type
     * 
     */
    public function search_detail_type(Request $request) 
    {
        $t = request('term');
        $type_details = [];
        $account_type = AccountType::find(request('account_type_id'));
        if ($account_type) {
            $type_details = AccountTypeDetail::where('system_rel', $account_type->system)
            ->when($t, fn($q) => $q->where('name', 'LIKE', "%{$t}%"))
            ->get();
            if (!$type_details->count()) {
                $type_details = AccountTypeDetail::where('category', $account_type->category)
                ->when($t, fn($q) => $q->where('name', 'LIKE', "%{$t}%"))
                ->get();
            }
        }
        return response()->json($type_details);
    }

    /**
     * Search next account number
     */
    public function search_next_account_no(Request $request)
    {
        $series = accounts_numbering(request('account_type'));
        $number = Account::where('account_type', request('account_type'))->max('number');
        if ($number) $series = $number + 1;

        return response()->json(['account_number' => $series]);
    }

    /**
     * Search Expense accounts 
     */
    public function account_search(Request $request)
    {
        if (!access()->allow('product_search')) return false;

        $k = $request->keyword;
        if ($request->type == 'Expense') {
            $accounts = Account::where('account_type', 'Expense')
                ->where(function ($q) use ($k) {
                    $q->where('holder', 'LIKE', '%' . $k . '%')->orWhere('number', 'LIKE', '%' . $k . '%');
                })->limit(6)->get(['id', 'holder AS name', 'number']);
        } else {
            $accounts = Account::where('holder', 'LIKE', '%' . $k . '%')
                ->orWhere('number', 'LIKE', '%' . $k . '%')
                ->limit(6)->get(['id', 'holder AS name', 'number']);
        }

        return response()->json($accounts);
    }


    /**
     * Profit And Loss (Income Statement)
     */
    public function profit_and_loss(Request $request)
    {
        $centralElectricals = auth()->user()->ins == 90 || auth()->user()->ins == 125;
        if ($centralElectricals) {
            return $this->profitAndLossSplitCog($request);
        }

        $dates = $request->only('start_date', 'end_date');
        $dates = array_map(fn($v) => date_for_database($v), $dates);
        $dates = array_values($dates);
        $classlist_id = $request->classlist_id;
        $classlists = Classlist::all();
        $reportPeriods = $this->reportPeriods();
        
        $accountTypeDetails = AccountTypeDetail::whereIn('category', ['Income', 'Expense'])
        ->whereHas('accounts', fn($q) => $q->whereHas('transactions', fn($q) => $q->where('debit', '>', 0)->orWhere('credit', '>', 0)))
        ->with(['accounts.transactions' => function($q) use($dates, $classlist_id) {
            $q->select('account_id', 'debit', 'credit');
            $q->when($dates, fn ($q) => $q->whereBetween('tr_date', $dates));
            $q->when($classlist_id, fn ($q) => $q->where('classlist_id', $classlist_id));
        }])
        ->get(['id', 'name', 'category', 'system_rel'])
        ->map(function($v) {
            $balance = 0;
            $accountBalance = 0;
            foreach ($v->accounts as $key => $account) {
                $debit = $account->transactions->sum('debit');
                $credit = $account->transactions->sum('credit');
                if ($account->account_type == 'Expense')
                    $accountBalance = round($debit-$credit,4);
                elseif ($account->account_type == 'Income') 
                    $accountBalance = round($credit-$debit,4);
                $v['accounts'][$key]['balance'] = $accountBalance;
                $balance += $accountBalance;
            }
            $v['balance'] = $balance;
            return $v;
        })
        ->filter(fn($v) => $v['balance'] != 0);
        
        if ($request->type == 'csv') {
            return $this->profitAndLossCsv($accountTypeDetails);
        }
        
        return new ViewResponse('focus.accounts.profit_&_loss', compact('accountTypeDetails', 'reportPeriods', 'classlists', 'dates'));
    }    

    /**
     * Profit and Loss Split COG
     */
    public function profitAndLossSplitCog(Request $request)
    {
        $dates = $request->only('start_date', 'end_date');
        $dates = array_map(fn($v) => date_for_database($v), $dates);
        $dates = array_values($dates);
        $classlist_id = $request->classlist_id;
        $classlists = Classlist::all();
        $reportPeriods = $this->reportPeriods();
        
        $accountTypeDetails = AccountTypeDetail::whereIn('category', ['Income', 'Expense'])
        ->whereHas('accounts', function($q) {
            $q->whereHas('transactions', function($q) {
                $q->doesntHave('productVariation');
                $q->where(fn($q) => $q->where('debit', '>', 0)->orWhere('credit', '>', 0));
            });
        })
        ->with([
            'accounts.transactions' => function($q) use($dates, $classlist_id) {
                $q->select('id', 'tid', 'account_id', 'debit', 'credit');
                $q->when($dates, fn ($q) => $q->whereBetween('tr_date', $dates));
                $q->when($classlist_id, fn ($q) => $q->where('classlist_id', $classlist_id));
            },
        ])
        ->get(['id', 'name', 'category', 'system',  'system_rel'])
        ->map(function($v) {
            $balance = 0;
            $accountBalance = 0;
            foreach ($v->accounts as $key => $account) {
                $debit = $account->transactions->sum('debit');
                $credit = $account->transactions->sum('credit');
                if ($account->account_type == 'Expense')
                    $accountBalance = round($debit-$credit,4);
                elseif ($account->account_type == 'Income') 
                    $accountBalance = round($credit-$debit,4);
                $v['accounts'][$key]['balance'] = $accountBalance;
                $balance += $accountBalance;
            }
            $v['balance'] = $balance;
            return $v;
        })
        ->filter(fn($v) => $v['balance'] != 0);
        
        if ($request->type == 'csv') {
            // return $this->profitAndLossCsv($accountTypeDetails);
            return '';
        }


        // COGS
        $inventoryAccountDetails = AccountTypeDetail::where('system', 'inventory_asset')
        ->whereHas('accounts', function($q) {
            $q->whereHas('transactions');
        })
        ->get(['id', 'name', 'category', 'system_rel']);

        $cogQuery = Transaction::whereHas('account', function($q) {
            $q->whereHas('account_type_detail', fn($q) => $q->where('system', 'inventory_asset'));
        });
        // opening stock
        $openingStockBal = (clone $cogQuery)->whereHas('manualjournal', function($q) {
            $q->whereHas('openingStock');
        })
        ->sum('debit');
        // purchases
        $purchasesBal = (clone $cogQuery) 
        ->where(fn($q) => $q->whereHas('grn_item')->orWhereHas('purchase_item'))
        ->sum('debit');
        // closing
        $closingStockBal = (clone $cogQuery)->sum(\DB::raw('debit-credit'));

        // Direct Expenses (Project: materials, labour, transport)
        $expenseAccTypeDetails = AccountTypeDetail::whereIn('category', ['Expense'])
        ->whereHas('accounts', function($q) {
            $q->whereHas('transactions', function($q) {
                $q->where('debit', '>', 0)->orWhere('credit', '>', 0);
            });
        })
        ->with(['accounts.transactions' => function($q) use($dates, $classlist_id) {
            $q->whereHas('invoice');
            $q->select('id', 'tid', 'account_id', 'debit', 'credit');
            $q->when($dates, fn ($q) => $q->whereBetween('tr_date', $dates));
            $q->when($classlist_id, fn ($q) => $q->where('classlist_id', $classlist_id));
        }])
        ->get(['id', 'name', 'category', 'system',  'system_rel'])
        ->map(function($v) {
            $balance = 0;
            $accountBalance = 0;
            foreach ($v->accounts as $key => $account) {
                $debit = $account->transactions->sum('debit');
                $credit = $account->transactions->sum('credit');
                if ($account->account_type == 'Expense')
                    $accountBalance = round($debit-$credit,4);
                elseif ($account->account_type == 'Income') 
                    $accountBalance = round($credit-$debit,4);
                $v['accounts'][$key]['balance'] = $accountBalance;
                $balance += $accountBalance;
            }
            $v['balance'] = $balance;
            return $v;
        })
        ->filter(fn($v) => $v['balance'] != 0);

        return new ViewResponse('focus.accounts.profit_&_loss_split_cog', compact(
            'accountTypeDetails', 'reportPeriods', 'classlists', 'dates',
            'inventoryAccountDetails', 'openingStockBal', 'purchasesBal', 'closingStockBal',
            'expenseAccTypeDetails',
        ));
    }

    /** 
     * Profit And Loss CSV Export
     * */
    public function profitAndLossCsv($accountTypeDetails)
    {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=Profit-And-Loss.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        $spaces = function ($n=1) {
            $space = ' ';
            for ($i=0; $i < $n; $i++) $space .= ' ';
            return $space;
        };
        $callback = function() use($accountTypeDetails, $spaces) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Income', ' ']);
            foreach ($accountTypeDetails->where('system_rel', 'income') as $detail) {
                fputcsv($file, [$spaces(4) . $detail->name, ' ']);
                foreach ($detail->accounts as $account) {
                    fputcsv($file, [$spaces(8) . "{$account->number}-{$account->holder}", $account->balance]);
                }
            }
            $totalIncome = $accountTypeDetails->where('system_rel', 'income')->sum('balance');
            fputcsv($file, ['Total Income',  $totalIncome]);
            fputcsv($file, [' ', ' ']);

            fputcsv($file, ['Cost Of Sales', ' ']);
            foreach ($accountTypeDetails->where('system_rel', 'cogs') as $detail) {
                fputcsv($file, [$spaces(4) . $detail->name, ' ']);
                foreach ($detail->accounts as $account) {
                    fputcsv($file, [$spaces(8) . "{$account->number}-{$account->holder}", $account->balance]);
                }
            }
            $totalCostOfSales = $accountTypeDetails->where('system_rel', 'cogs')->sum('balance');
            fputcsv($file, ['Total Cost Of Sales',  $totalCostOfSales]);
            fputcsv($file, [' ', ' ']);

            $grossProfit = $totalIncome-$totalCostOfSales;
            fputcsv($file, ['Gross Profit', $grossProfit]);
            fputcsv($file, [' ', ' ']);

            fputcsv($file, ['Other Income', ' ']);
            foreach ($accountTypeDetails->where('system_rel', 'other_income') as $detail) {
                fputcsv($file, [$spaces(4) . $detail->name, ' ']);
                foreach ($detail->accounts as $account) {
                    fputcsv($file, [$spaces(8) . "{$account->number}-{$account->holder}", $account->balance]);
                }
            }
            $totalOtherIncome = $accountTypeDetails->where('system_rel', 'other_income')->sum('balance');
            fputcsv($file, ['Total Other Income',  $totalOtherIncome]);
            fputcsv($file, [' ', ' ']);

            fputcsv($file, ['Expense', ' ']);
            foreach ($accountTypeDetails->where('system_rel', 'expense') as $detail) {
                fputcsv($file, [$spaces(4) . $detail->name, ' ']);
                foreach ($detail->accounts as $account) {
                    fputcsv($file, [$spaces(8) . "{$account->number}-{$account->holder}", $account->balance]);
                }
            }
            $totalExpense = $accountTypeDetails->where('system_rel', 'expense')->sum('balance');
            fputcsv($file, ['Total Expense',  $totalExpense]);
            fputcsv($file, [' ', ' ']);

            fputcsv($file, ['Other Expense', ' ']);
            foreach ($accountTypeDetails->where('system_rel', 'other_expense') as $detail) {
                fputcsv($file, [$spaces(4) . $detail->name, ' ']);
                foreach ($detail->accounts as $account) {
                    fputcsv($file, [$spaces(8) . "{$account->number}-{$account->holder}", $account->balance]);
                }
            }
            $totalOtherExpense = $accountTypeDetails->where('system_rel', 'other_expense')->sum('balance');
            fputcsv($file, ['Total Other Expense',  $totalOtherExpense]);
            fputcsv($file, [' ', ' ']);

            $netProfit = $grossProfit+$totalOtherIncome-$totalExpense-$totalOtherExpense;
            fputcsv($file, ['Net Profit', $netProfit]);
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Cash Flow Statement
     */
    public function cash_flow_statement(Request $request)
    {
        $date_range = $request->only('start_date', 'end_date');
        $date_range = array_map(fn($v) => date_for_database($v), $date_range);
        $date_range = array_values($date_range);

        $q = Account::query()->whereHas('transactions', function ($q) use ($date_range) {
            $q->when($date_range, fn($q) => $q->whereBetween('tr_date', $date_range));
        })->with(['account_type_detail' => fn($q) => $q->select('id', 'system')]);

        if ($date_range) {
        } else {
            // set based on current fiscal month
            $yr = date('Y');
            $month = '01';
            $company = auth()->user()->business;
            if ($company && $company->fiscal_month) 
                $month = date('m', strtotime($company->fiscal_month));
            $startCurrFiscalYear = "{$yr}-{$month}-01";
            $endCurrFiscalYear = date('Y-m-t', strtotime('+11 months', strtotime($startCurrFiscalYear)));
            // dd($startCurrFiscalYear, $endCurrFiscalYear);
            
            // $q = Account::query()->whereHas('transactions', function ($q) use ($startCurrFiscalYear, $endCurrFiscalYear) {
            //     $q->whereBetween('tr_date', [$startCurrFiscalYear, $endCurrFiscalYear]);
            // })->with(['account_type_detail' => fn($q) => $q->select('id', 'system')]);
        }
        
        $q1 = clone $q;
        $operating_accounts = $q1->whereHas('account_type_detail', fn($q) => $q->whereIn('system', [
            'depreciation_expense', 'amortization_expense', 'asset_sale_gain', 'asset_sale_loss', // non-cash adjustment
            'receivable', 'payable', 'inventory_asset', 'prepaid_expenses', 'accrued_liability', 'payroll_liability', 'credit_card', // changes in working capital
            'interest_expense', 'sale_tax_payable', 'vat_payable', // other operating cash flows (interests and taxes)
        ]))->get();
        $q2 = clone $q;
        $investing_accounts = $q2->whereHas('account_type_detail', fn($q) => $q->whereIn('system', [
            'furniture_and_fixture', 'machinery_and_equipment', 'vehicles', 'property', // Property, Plant & Equipment
            'loan_receivable','intangible_asset','equity_security','debt_security','affiliate_security','real_estate','mutual_funds', // long-term investment
            'certificate_of_deposits','minority_interest','government_security','private_equity','capitalized_development_costs', // long-term investment
            'st_bonds','st_certificate_of_deposit','treasury_bills','commercial_paper','st_loan_receivable','dividends_receivable', // short term investment
            'interest_receivable','trading_securities','st_mutual_funds', // short term investment
        ]))->get();
        $q3 = clone $q;
        $financing_accounts = $q3->whereHas('account_type_detail', fn($q) => $q->whereIn('system', [
            'common_stock', 'preferred_stock', 'bonds_payable', 'notes_payable', 'loan_payable', // cash in
            'treasury_stock', 'dividends_payable', // cash out
        ]))->get();

        // beginning cash (ending balance of the prior financial period)
        $beginning_cash = 0;
        if (@$date_range[0]) {
            $last_date_prev_month = (Carbon::parse($date_range[0])->subMonth()->endOfMonth())->toDateString();
            $q4 = Account::query()->whereHas('transactions', function ($q) use ($startPrevFiscalYear, $endPrevFiscalYear) {
                $q->where('tr_date', '<=', $last_date_prev_month);
            })->with(['account_type_detail' => fn($q) => $q->select('id', 'system')]);
            $cash_and_equiv_accounts = $q4->whereHas('account_type_detail', fn($q) => $q->whereIn('system', [
                'cash', 'checking', 'saving', 'money_market', 'treasury_bills'
            ]))->get();
            foreach ($cash_and_equiv_accounts as $key => $account) {
                if ($last_date_prev_month) {
                    $balance = $account->transactions()
                        ->where('tr_date', '<=', $last_date_prev_month)
                        ->sum(\DB::raw('debit - credit'));
                    $beginning_cash += round($balance, 2);
                }
            }
        } else {
            // set based on fiscal month
            $yr = date('Y')-1;
            $month = '01';
            $company = auth()->user()->business;
            if ($company && $company->fiscal_month) 
                $month = date('m', strtotime($company->fiscal_month));
            $startPrevFiscalYear = "{$yr}-{$month}-01";
            $endPrevFiscalYear = date('Y-m-t', strtotime('+11 months', strtotime($startPrevFiscalYear)));
            $last_date_prev_month = $endPrevFiscalYear;
            
            $q4 = Account::query()->whereHas('transactions', function ($q) use ($startPrevFiscalYear, $endPrevFiscalYear) {
                $q->whereBetween('tr_date', [$startPrevFiscalYear, $endPrevFiscalYear]);
            })->with(['account_type_detail' => fn($q) => $q->select('id', 'system')]);
            $cash_and_equiv_accounts = $q4->whereHas('account_type_detail', fn($q) => $q->whereIn('system', [
                'cash', 'checking', 'saving', 'money_market', 'treasury_bills'
            ]))->get();
            foreach ($cash_and_equiv_accounts as $key => $account) {
                $balance = $account->transactions()
                    ->whereBetween('tr_date', [$startPrevFiscalYear, $endPrevFiscalYear])
                    ->sum(\DB::raw('debit - credit'));
                $beginning_cash += round($balance, 2);
            }
        }
        
        // net income
        $net_income = 0;
        if (@$date_range[1]) {
            $profit_and_loss_accounts = Account::whereIn('account_type', ['Income', 'Expense'])
            ->whereHas('transactions', function ($q) use ($date_range) {
                $q->when(@$date_range[1], fn($q) => $q->where('tr_date', '<=', $date_range[1]));
            })->get();
            foreach ($profit_and_loss_accounts as $key => $account) {
                $balance = 0;
                if ($account->account_type == 'Expense') {
                    $balance = $account->transactions()
                        ->when(@$date_range[1], fn($q) => $q->where('tr_date', '<=', $date_range[1]))
                        ->sum(\DB::raw('debit - credit'));
                    $net_income -= round($balance, 4);
                } elseif ($account->account_type == 'Income') {
                    $balance = $account->transactions()
                        ->when(@$date_range[1], fn($q) => $q->where('tr_date', '<=', $date_range[1]))
                        ->sum(\DB::raw('credit - debit'));
                    $net_income += round($balance, 4);
                }
            }
        } elseif (@$startPrevFiscalYear && @$endPrevFiscalYear) {
            $profit_and_loss_accounts = Account::whereIn('account_type', ['Income', 'Expense'])
                ->whereHas('transactions', function ($q) use ($startPrevFiscalYear, $endPrevFiscalYear) {
                    $q->whereBetween('tr_date', [$startPrevFiscalYear, $endPrevFiscalYear]);
                })->get();
            foreach ($profit_and_loss_accounts as $key => $account) {
                $balance = 0;
                if ($account->account_type == 'Expense') {
                    $balance = $account->transactions()
                        ->whereBetween('tr_date', [$startPrevFiscalYear, $endPrevFiscalYear])
                        ->sum(\DB::raw('debit - credit'));
                    $net_income -= round($balance, 4);
                } elseif ($account->account_type == 'Income') {
                    $balance = $account->transactions()
                        ->whereBetween('tr_date', [$startPrevFiscalYear, $endPrevFiscalYear])
                        ->sum(\DB::raw('credit - debit'));
                    $net_income += round($balance, 4);
                }
            }
        }

        $params = compact('date_range', 'operating_accounts', 'investing_accounts', 'financing_accounts', 'beginning_cash', 'net_income');
        if ($request->type == 'p') return $this->print_document('cash_flow_statement', $params);

        return new ViewResponse('focus.accounts.cash_flow_statement', $params);
    }

    /**
     * Balance Sheet
     */
    public function balance_sheet(Request $request)
    {
        $date = request('end_date')? date_for_database(request('end_date')) : '';

        $dates = $request->only('start_date', 'end_date');
        $dates = array_map(fn($v) => date_for_database($v), $dates);
        $dates = array_values($dates);
        $classlist_id = $request->classlist_id;
        $classlists = Classlist::all();

        // modify report periods
        $reportPeriods = $this->reportPeriods();
        $trxStartDate = optional(Transaction::first(['tr_date']))->tr_date;
        $fiscalMonth = optional(auth()->user()->business)->fiscal_month ?: date('Y-01-01');
        $currFiscalMonth = str_replace(date('Y', strtotime($fiscalMonth)), date('Y'), $fiscalMonth);
        foreach ($reportPeriods as $key => $value) {
            if ($key == 'lastYear') {
                $endDate = date('Y-m-d', strtotime($currFiscalMonth . ' -1 days'));
                $reportPeriods[$key] = [dateFormat($trxStartDate), dateFormat($endDate)];
            } else if ($key == 'thisYear') {
                $startDate = $currFiscalMonth;
                $endDate = date('Y-m-d', strtotime($startDate . ' +1 years'));
                $endDate = date('Y-m-d', strtotime($endDate . ' -1 days'));
                $reportPeriods[$key] = [dateFormat($trxStartDate), dateFormat($endDate)];
            } else {
                $reportPeriods[$key][0] = dateFormat($trxStartDate);
            }
        }

        $netProfit = Account::whereIn('account_type', ['Income', 'Expense'])
        ->whereHas('transactions', fn($q) =>  $q->when($dates, fn($q) => $q->whereBetween('tr_date', $dates)))
        ->with([
            'transactions' => function($q) use($dates) {
                $q->select('id', 'account_id', 'debit', 'credit')
                ->when($dates, fn($q) => $q->whereBetween('tr_date', $dates));
            },
        ])
        ->get(['id', 'account_type', 'holder'])
        ->reduce(function($prev, $curr) {
            $debit = $curr->transactions->sum('debit');
            $credit = $curr->transactions->sum('credit');
            if ($curr->account_type == 'Income') {
                $balance = round($credit-$debit,4);
                $prev += $balance;
            } else {
                $balance = round($debit-$credit,4);
                $prev -= $balance;
            }
            return $prev;
        }, 0);

        $accountTypeDetails = AccountTypeDetail::whereIn('category', ['Asset', 'Liability', 'Equity'])
        ->whereHas('accounts', fn($q) => $q->whereHas('transactions', fn($q) => $q->where('debit', '>', 0)->orWhere('credit', '>', 0)))
        ->with(['accounts.transactions' => function($q) use($dates) {
            $q->select('account_id', 'debit', 'credit');
            $q->when($dates, fn($q) => $q->whereBetween('tr_date', $dates));
        }])
        ->get(['id', 'name', 'category', 'system_rel'])
        ->map(function($v) {
            $balance = 0;
            $accountBalance = 0;
            foreach ($v->accounts as $key => $account) {
                $debit = $account->transactions->sum('debit');
                $credit = $account->transactions->sum('credit');
                if ($account->account_type == 'Asset')
                    $accountBalance = round($debit-$credit,4);
                else $accountBalance = round($credit-$debit,4);
                $v['accounts'][$key]['balance'] = $accountBalance;
                $balance += $accountBalance;
            }
            $v['balance'] = $balance;
            return $v;
        })
        ->filter(fn($v) => $v['balance'] != 0);

        // print balance_sheet
        // if ($request->type == 'p') return $this->print_document('balance_sheet', compact('accounts', 'dates', 'net_profit'));
        if ($request->type == 'csv') {
            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=Balance-Sheet.csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];
            $spaces = function ($n=1) {
                $space = ' ';
                for ($i=0; $i < $n; $i++) $space .= ' ';
                return $space;
            };
            $callback = function() use($accountTypeDetails, $netProfit, $spaces) {
                $file = fopen('php://output', 'w');
                /** Assets */
                fputcsv($file, ['Assets', ' ']);
                fputcsv($file, [$spaces(4) . 'Current Assets', ' ']);
                fputcsv($file, [$spaces(8) . 'Bank Accounts', ' ']);
                foreach ($accountTypeDetails->whereIn('system_rel', ['cash', 'bank']) as $detail) {
                    fputcsv($file, [$spaces(12) . $detail->name, ' ']);
                    foreach ($detail->accounts->where('balance', '!=', 0) as $account) {
                        fputcsv($file, [$spaces(16) . "{$account->number}-{$account->holder}", $account->balance]);
                    }
                }
                $totalBankAccounts = $accountTypeDetails->whereIn('system_rel', ['cash', 'bank'])->sum('balance');
                fputcsv($file, [$spaces(8) . 'Total Bank Accounts',  $totalBankAccounts]);
                fputcsv($file, [' ', ' ']);

                fputcsv($file, [$spaces(8) . 'Accounts Receivable', ' ']);
                foreach ($accountTypeDetails->whereIn('system_rel', ['receivable', 'loan']) as $detail) {
                    fputcsv($file, [$spaces(12) . $detail->name, ' ']);
                    foreach ($detail->accounts->where('balance', '!=', 0) as $account) {
                        fputcsv($file, [$spaces(16) . "{$account->number}-{$account->holder}", $account->balance]);
                    }
                }
                $totalAR = $accountTypeDetails->whereIn('system_rel', ['receivable', 'loan'])->sum('balance');
                fputcsv($file, [$spaces(8) . 'Total Accounts Receivable',  $totalAR]);
                fputcsv($file, [' ', ' ']);

                fputcsv($file, [$spaces(8) . 'Other Current Assets', ' ']);
                foreach ($accountTypeDetails->whereIn('system_rel', ['current_asset']) as $detail) {
                    fputcsv($file, [$spaces(12) . $detail->name, ' ']);
                    foreach ($detail->accounts->where('balance', '!=', 0) as $account) {
                        fputcsv($file, [$spaces(16) . "{$account->number}-{$account->holder}", $account->balance]);
                    }
                }
                $totalOtherCurrentAssets = $accountTypeDetails->whereIn('system_rel', ['current_asset'])->sum('balance');
                fputcsv($file, [$spaces(8) . 'Total Other Current Assets',  $totalOtherCurrentAssets]);
                fputcsv($file, [' ', ' ']);

                fputcsv($file, [$spaces(8) . 'Fixed Assets', ' ']);
                foreach ($accountTypeDetails->whereIn('system_rel', ['fixed_asset']) as $detail) {
                    fputcsv($file, [$spaces(12) . $detail->name, ' ']);
                    foreach ($detail->accounts->where('balance', '!=', 0) as $account) {
                        fputcsv($file, [$spaces(16) . "{$account->number}-{$account->holder}", $account->balance]);
                    }
                }
                $totalFixedAssets = $accountTypeDetails->whereIn('system_rel', ['fixed_asset'])->sum('balance');
                fputcsv($file, [$spaces(8) . 'Total Fixed Assets',  $totalFixedAssets]);
                fputcsv($file, [' ', ' ']);

                fputcsv($file, [$spaces(8) . 'Other Assets', ' ']);
                foreach ($accountTypeDetails->whereIn('system_rel', ['other_asset']) as $detail) {
                    fputcsv($file, [$spaces(12) . $detail->name, ' ']);
                    foreach ($detail->accounts->where('balance', '!=', 0) as $account) {
                        fputcsv($file, [$spaces(16) . "{$account->number}-{$account->holder}", $account->balance]);
                    }
                }
                $totalOtherAssets = $accountTypeDetails->whereIn('system_rel', ['other_asset'])->sum('balance');
                fputcsv($file, [$spaces(8) . 'Total Other Assets',  $totalOtherAssets]);
                fputcsv($file, [' ', ' ']);

                $totalAssets = $accountTypeDetails->where('category', 'Asset')->sum('balance');
                fputcsv($file, ['Total Assets',  $totalAssets]);
                fputcsv($file, [' ', ' ']);

                /** Liabilities */
                fputcsv($file, ['Liabilities', ' ']);
                fputcsv($file, [$spaces(4) . 'Current Liabilities', ' ']);
                fputcsv($file, [$spaces(8) . 'Accounts Payable', ' ']);
                foreach ($accountTypeDetails->whereIn('system_rel', ['payable']) as $detail) {
                    fputcsv($file, [$spaces(12) . $detail->name, ' ']);
                    foreach ($detail->accounts->where('balance', '!=', 0) as $account) {
                        fputcsv($file, [$spaces(16) . "{$account->number}-{$account->holder}", $account->balance]);
                    }
                }
                $totalAP = $accountTypeDetails->whereIn('system_rel', ['payable'])->sum('balance');
                fputcsv($file, [$spaces(8) . 'Total Accounts Payable',  $totalAP]);
                fputcsv($file, [' ', ' ']);

                fputcsv($file, [$spaces(8) . 'Payroll Liabilities', ' ']);
                foreach ($accountTypeDetails->whereIn('system_rel', ['payroll_liability']) as $detail) {
                    fputcsv($file, [$spaces(12) . $detail->name, ' ']);
                    foreach ($detail->accounts->where('balance', '!=', 0) as $account) {
                        fputcsv($file, [$spaces(16) . "{$account->number}-{$account->holder}", $account->balance]);
                    }
                }
                $totalPayrollLiabilities = $accountTypeDetails->whereIn('system_rel', ['payroll_liability'])->sum('balance');
                fputcsv($file, [$spaces(8) . 'Total Payroll Liabilities',  $totalPayrollLiabilities]);
                fputcsv($file, [' ', ' ']);

                fputcsv($file, [$spaces(8) . 'Credit Cards', ' ']);
                foreach ($accountTypeDetails->whereIn('system', ['credit_card']) as $detail) {
                    fputcsv($file, [$spaces(12) . $detail->name, ' ']);
                    foreach ($detail->accounts->where('balance', '!=', 0) as $account) {
                        fputcsv($file, [$spaces(16) . "{$account->number}-{$account->holder}", $account->balance]);
                    }
                }
                $totalCreditCards = $accountTypeDetails->whereIn('system', ['credit_card'])->sum('balance');
                fputcsv($file, [$spaces(8) . 'Total Credit Cards',  $totalCreditCards]);
                fputcsv($file, [' ', ' ']);

                fputcsv($file, [$spaces(8) . 'Other Current Liabilities', ' ']);
                foreach ($accountTypeDetails->whereIn('system_rel', ['other_current_liability']) as $detail) {
                    fputcsv($file, [$spaces(12) . $detail->name, ' ']);
                    foreach ($detail->accounts->where('balance', '!=', 0) as $account) {
                        fputcsv($file, [$spaces(16) . "{$account->number}-{$account->holder}", $account->balance]);
                    }
                }
                $totalOtherCurrentLiabilities = $accountTypeDetails->whereIn('system_rel', ['other_current_liability'])->sum('balance');
                fputcsv($file, [$spaces(8) . 'Total Other Current Liabilities',  $totalOtherCurrentLiabilities]);
                fputcsv($file, [' ', ' ']);

                fputcsv($file, [$spaces(8) . 'Long-Term Liabilities', ' ']);
                foreach ($accountTypeDetails->whereIn('system_rel', ['loan', 'long_term_liability']) as $detail) {
                    fputcsv($file, [$spaces(12) . $detail->name, ' ']);
                    foreach ($detail->accounts->where('balance', '!=', 0) as $account) {
                        fputcsv($file, [$spaces(16) . "{$account->number}-{$account->holder}", $account->balance]);
                    }
                }
                $totalLongtermLiabilities = $accountTypeDetails->whereIn('system_rel', ['loan', 'long_term_liability'])->sum('balance');
                fputcsv($file, [$spaces(8) . 'Total Long-Term Liabilities',  $totalLongtermLiabilities]);
                fputcsv($file, [' ', ' ']);

                $totalLiabilities = $accountTypeDetails->where('category', 'Liability')->sum('balance');
                fputcsv($file, ['Total Liabilities',  $totalLiabilities]);
                fputcsv($file, [' ', ' ']);

                /** Equity  */
                fputcsv($file, ['Equity', ' ']);
                foreach (['equity', 'owner_equity', 'retained_earning'] as $relation) {
                    foreach ($accountTypeDetails->whereIn('system_rel', [$relation]) as $detail) {
                        fputcsv($file, [$spaces(4) . $detail->name, ' ']);
                        foreach ($detail->accounts->where('balance', '!=', 0) as $account) {
                            fputcsv($file, [$spaces(8) . "{$account->number}-{$account->holder}", $account->balance]);
                        }
                    }
                }
                fputcsv($file, [$spaces(4) . 'Net Profit', $netProfit]);
                fputcsv($file, [' ', ' ']);
                
                $totalEquity = $accountTypeDetails->whereIn('system_rel', ['equity', 'owner_equity', 'retained_earning'])->sum('balance');
                $totalEquity += $netProfit;
                fputcsv($file, ['Total Equity',  $totalEquity]);
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        return new ViewResponse('focus.accounts.balance_sheet', 
            compact('accountTypeDetails', 'netProfit', 'reportPeriods', 'dates')
        );
    }

    /**
     * Trial Balance
     */
    public function trial_balance(Request $request)
    {
        $classlist_id = $request->classlist_id;
        $date = $request->end_date? date_for_database($request->end_date) : '';
        $dates = array_values($request->only('start_date', 'end_date'));
        $dates = array_map(fn($v) => date_for_database($v), $dates);
        
        // modify report periods
        $reportPeriods = $this->reportPeriods();
        $trxStartDate = optional(Transaction::first(['tr_date']))->tr_date;
        $fiscalMonth = optional(auth()->user()->business)->fiscal_month ?: date('Y-01-01');
        $currFiscalMonth = str_replace(date('Y', strtotime($fiscalMonth)), date('Y'), $fiscalMonth);
        foreach ($reportPeriods as $key => $value) {
            $reportPeriods[$key][0] = dateFormat($trxStartDate);
            if ($key == 'lastYear') {
                $endDate = date('Y-m-d', strtotime($currFiscalMonth . ' -1 days'));
                $reportPeriods[$key][1] = dateFormat($endDate);
            } else if ($key == 'thisYear') {
                $startDate = $currFiscalMonth;
                $endDate = date('Y-m-d', strtotime($startDate . ' +1 years'));
                $endDate = date('Y-m-d', strtotime($endDate . ' -1 days'));
                $reportPeriods[$key][1] = dateFormat($endDate);
            } 
        }

        $accounts = Account::whereHas('transactions')
        ->with([
            'account_type_detail' => fn($q) => $q->select('id', 'name', 'system_rel'),
            'transactions' => function($q) use($dates, $trxStartDate) {
                $q->select('account_id', 'debit', 'credit');
                if (count($dates) == 2) $q->whereBetween('tr_date', $dates);
                elseif ($trxStartDate) $q->where('tr_date', '>=', $trxStartDate);                
            },
        ])
        ->get(['id', 'number', 'holder', 'account_type', 'account_type_detail_id'])
        ->map(function($v) {
            $debit = $v->transactions->sum('debit');
            $credit = $v->transactions->sum('credit');
            $balance = 0;
            if (in_array($v->account_type, ['Asset', 'Expense']))
                $balance += round($debit-$credit,4);
            else $balance += round($credit-$debit,4);
            $v['balance'] = $balance;
            return $v;
        })
        ->filter(fn($v) => $v['balance'] != 0)
        ->sortBy(function($v) {
            return array_search($v->account_type_detail->system_rel, [
                'income','cogs','other_income','expense','other_expense', // Profit And Loss Accounts
                'cash', 'bank', 'receivable', 'loan', 'current_asset', 'other_asset', 'fixed_asset',  // Balance Sheet Asset Accounts
                'payable', 'credit_card', 'loan', 'payroll_liability', 'other_current_liability', 'long_term_liability', // Balance Sheet Liability Accounts
                'equity', 'owner_equity', 'retained_earning' // Balance Sheet Equity Accounts
            ]);
        });

        if ($request->type == 'csv') {
            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=Trial-Balance.csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];
            $callback = function() use($accounts) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Number', 'Account', 'Debit', 'Credit']);
                foreach ($accounts as $i => $account) {
                    $name = @$account->account_type_detail->name . ': ' . $account->holder;
                    $row = [$account->number, $name, 0, 0];
                    $isDebitAccount = in_array($account->account_type, ['Asset', 'Expense']);
                    if ($isDebitAccount) array_splice($row, 2, 1, $account->balance);
                    else array_splice($row, 3, 1, $account->balance);
                    fputcsv($file, $row);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        return new ViewResponse('focus.accounts.trial_balance', compact('accounts', 'reportPeriods', 'dates'));
    }

    /**
     * Print document
     */
    public function print_document(string $name, ...$params)
    {   //dd($params);
        $account_types = ['Assets', 'Equity', 'Expenses', 'Liabilities', 'Income'];
        $html = view('focus.accounts.print_' . $name, $account_types, ...$params)->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        $headers = array(
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        return Response::stream($pdf->Output($name . '.pdf', 'I'), 200, $headers);
    }

    /**
     * Project Gross Profit Index
     */
    public function project_gross_profit()
    {
        $customers = Customer::whereHas('projects')->get(['id', 'company']);

        return new ViewResponse('focus.accounts.project_gross_profit', compact('customers'));
    }

    /**
     * Cashbook Index
     */
    public function cashbook()
    {
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system_rel', ['bank', 'cash']))
            ->whereHas('transactions')
            ->get(['id', 'holder', 'number']);

        return new ViewResponse('focus.accounts.cashbook', compact('accounts'));
    }

    /** 
     * Cashbook Account (cash ledger)
     * */
    public function cashbookTransactions()
    {
        $q = Transaction::query();
        $q->whereHas('account', function ($q) {
            $q->whereHas('account_type_detail', fn($q) => $q->whereIn('system_rel', ['bank', 'cash']));
        });
        
        $q->when(request('account_id'), fn($q) => $q->where('account_id', request('account_id')))
        ->when(request('tr_type') == 'receipt', fn($q) => $q->where('debit', '>', 0))
        ->when(request('tr_type') == 'payment', fn($q) => $q->where('credit', '>', 0))
        ->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('tr_date', [
                date_for_database(request('start_date')),
                date_for_database(request('end_date')),
            ]);
        });

        $q->with([
            'deposit' => fn($q) => $q->select(['id', 'tid']),
            'bill_payment' => fn($q) => $q->select(['id', 'tid']),
            'transfer' => fn($q) => $q->select(['id', 'tid']),
            'manualjournal' => fn($q) => $q->select(['id', 'tid']),
            'charge' => fn($q) => $q->select(['id', 'tid']),
        ]);

        return $q;
    }

    /**
     * General Ledger
     */
    public function generalLedger(Request $request)
    {
        $classlists = Classlist::all();
        $selectAccounts = Account::whereHas('transactions')->get(['id', 'number', 'holder']);
        $modDates = [
            request('start_date_mod')? date_for_database(request('start_date_mod')) : null,
            request('end_date_mod')? date_for_database(request('end_date_mod')) : null,
        ];
        $trxStartDate = optional(Transaction::first(['tr_date']))->tr_date;
        
        // request inputs
        $accountId = $request->account_id? explode(',', $request->account_id) : [];
        $trType = $request->tr_type? explode(',', $request->tr_type) : [];
        $classlistId = $request->classlist_id;
        $amountType = $request->amount_type;

        $startDate = request('start_date', date('01-m-Y'));
        $lastDay = Carbon::now()->endOfMonth()->format('d-m-Y');
        $endDate = request('end_date', $lastDay);    
        $dates = [$startDate, $endDate];
        if (array_filter($dates)) {
            $dates = array_map(fn($v) => date_for_database($v), $dates);
            $accounts = Account::query()
                ->when($classlistId, fn($q) => $q->whereHas('transactions', fn($q) => $q->where('classlist_id', $classlistId)))
                ->when($accountId, fn($q) => $q->whereIn('id', $accountId))
                ->when($startDate && $endDate, function($q) use($dates, $trType, $amountType) {
                    $q->whereHas('transactions', function($q) use($dates, $trType, $amountType) {
                        $q->whereBetween('tr_date', $dates);
                        $q->when($trType, fn($q) => $q->whereIn('tr_type', $trType));
                        $q->when($amountType == 'Debit', fn($q) => $q->where('debit', '>', 0));
                        $q->when($amountType == 'Credit', fn($q) => $q->where('credit', '>', 0));
                    });
                })
                ->when(count(array_filter($modDates)) == 2, function($q) use($modDates, $trType, $amountType) {
                    $q->whereHas('transactions', function($q) use($modDates, $trType, $amountType) {
                        $q->whereDate('updated_at', '>=', $modDates[0]);
                        $q->whereDate('updated_at', '<=', $modDates[1]);
                        $q->when($trType, fn($q) => $q->whereIn('tr_type', $trType));
                        $q->when($amountType == 'Debit', fn($q) => $q->where('debit', '>', 0));
                        $q->when($amountType == 'Credit', fn($q) => $q->where('credit', '>', 0));
                    });
                })
                ->whereHas('account_type_detail')
                ->with([
                    'account_type_detail:id,name,system_rel',
                    'transactions' => function ($q) use($trxStartDate) {
                        $q->whereDate('tr_date', '>=', $trxStartDate)
                          ->with([
                              'customer:id,company,name',
                              'supplier:id,company,name',
                              'bill:id,tid',
                              'bill_payment:id,tid',
                              'invoice:id,tid',
                              'invoice_payment:id,tid',
                              'creditnote:id,tid',
                              'debitnote:id,tid',
                              'withholding:id,tid',
                              'charge:id,tid',
                              // missing tid
                              'opening_stock:id',
                              'grn:id,tid',
                              // missing tid
                              'stock_adj:id', 
                              'stock_issue:id,tid',
                              'sale_return:id,tid',
                              'transfer:id,tid',
                              'manualjournal:id,tid',
                          ]);
                    },
                ])
                ->get(['id', 'number', 'holder', 'account_type', 'account_type_detail_id'])
                ->sortBy(function($v) {
                    return array_search($v->account_type_detail->system_rel, [
                        'income','cogs','other_income','expense','other_expense', // Profit And Loss Accounts
                        'cash', 'bank', 'receivable', 'loan', 'current_asset', 'other_asset', 'fixed_asset',  // Balance Sheet Asset Accounts
                        'payable', 'credit_card', 'loan', 'payroll_liability', 'other_current_liability', 'long_term_liability', // Balance Sheet Liability Accounts
                        'equity', 'owner_equity', 'retained_earning' // Balance Sheet Equity Accounts
                    ]);
                })
                ->map(function($v) use($dates, $classlistId, $accountId, $trType, $amountType, $trxStartDate) {
                    if ($classlistId) $v->transactions = $v->transactions->where('classlist_id', $classlistId);
                    if ($accountId) $v->transactions = $v->transactions->whereIn('account_id', $accountId);
                    if ($trType) $v->transactions = $v->transactions->whereIn('tr_type', $trType);
                    if ($amountType == 'Debit') $v->transactions =  $v->transactions->where('debit', '>', 0);
                    if ($amountType == 'Credit') $v->transactions =  $v->transactions->where('credit', '>', 0);
    
                    // balance brought foward
                    if (count(array_filter($dates)) == 2) {
                        $transactions = $v->transactions;
                        $transBroughtFoward = $transactions->where('tr_date', '<', $dates[0]);
                        // dd($transBroughtFoward->toArray());
                        $debits = $transBroughtFoward->sum('debit');
                        $credits = $transBroughtFoward->sum('credit');
                        if (in_array($v->account_type, ['Asset', 'Expense'])) $v->balance = round($debits-$credits, 4);
                        else $v->balance = round($credits-$debits, 4);
                        // rest of transactions
                        $v->transactions = $transactions->whereBetween('tr_date', $dates)->sortBy('tr_date');
                    }
                    return $v;
                });
        } else {
            $dates = [];
            $accounts = collect();
        }

        if ($request->type == 'csv') {
            return $this->generalLedgerCsv($accounts, $dates);
        }
                        
        return view('focus.accounts.general_ledger', compact('selectAccounts', 'accounts', 'classlists', 'dates', 'trType', 'accountId'));
    }

    public function generalLedgerCsv($accounts, $dates)
    {
        $callback = function() use($accounts, $dates) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Type', 'Date', 'Ref No.', 'Payee', 'Note', 'Amount', 'Balance']);
            foreach ($accounts as $account) {
                $accountBalance = $dates? $account->balance : 0;
                $movingBalance = $accountBalance;
                $totalAmount = 0;
    
                // ledger account row 
                $row = ["{$account->number}: {$account->holder}", ...array_fill(0, 5, ' '), $accountBalance];
                fputcsv($file, $row);

                // transactions row
                $trs = $account->transactions;
                foreach ($trs as $tr) {
                    if (in_array($account->account_type, ['Asset', 'Expense'])) {
                        $amount = $tr->debit > 0? +$tr->debit : -$tr->credit;
                    } else $amount = $tr->credit > 0? +$tr->credit : -$tr->debit;
                    $movingBalance += $amount;
                    $totalAmount += $amount;
    
                    $customer = @$tr->customer->company ?: @$tr->customer->name;
                    $supplier = @$tr->supplier->name ?: @$tr->supplier->company;
                    $type = '';
                    $tid = '';
                    if ($tr->bill) {
                        $tid = $tr->bill->tid;
                        $type = 'Bill';
                    } elseif ($tr->bill_payment) {
                        $tid = $tr->bill_payment->tid;
                        $type = 'Bill Payment';
                    } elseif ($tr->invoice) {
                        $tid = $tr->invoice->tid;
                        $type = 'Sale';
                    } elseif ($tr->invoice_payment) {
                        $tid = $tr->invoice_payment->tid;
                        $type = 'Receive Payment';
                    } elseif ($tr->creditnote) {
                        $tid = $tr->creditnote->tid;
                        $type = 'Credit Note';
                    } elseif ($tr->debitnote) {
                        $tid = $tr->debitnote->tid;
                        $type = 'Debit Note';
                    } elseif ($tr->withholding) {
                        $tid = $tr->withholding->tid;
                        $type = 'Tax Withholding';
                    } elseif ($tr->charge) {
                        $tid = $tr->charge->tid;
                        $type = 'Charge';
                    } elseif ($tr->grn) {
                        $tid = $tr->grn->tid;
                        $type = 'Goods Receive Note';
                    } elseif ($tr->stock_issue) {
                        $tid = $tr->stock_issue->tid;
                        $type = 'Stock Issue';
                    } elseif ($tr->sale_return) {
                        $tid = $tr->sale_return->tid;
                        $type = 'Stock Return';
                    } elseif ($tr->transfer) {
                        $tid = $tr->transfer->tid;
                        $type = 'Transfer';
                    } elseif ($tr->manualjournal) {
                        $tid = $tr->manualjournal->tid;
                        $type = 'Journal Entry';
                    } elseif ($tr->stock_adj) {
                        // $tid = $tr->manualjournal->tid;
                        // $tid = '<a href="'.route('biller.journals.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                        $type = 'Stock Adjustment';
                    } elseif ($tr->opening_stock) {
                        // $tid = $tr->manualjournal->tid;
                        // $tid = '<a href="'.route('biller.journals.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                        $type = 'Opening Stock';
                    }
                    $row = ["    {$type}", dateFormat($tr->tr_date), $tid, ($customer ?: $supplier), $tr->note, numberFormat($amount), numberFormat($movingBalance)];
                    fputcsv($file, $row);
                }

                // aggregate balance row 
                $row = ["Total {$account->holder}", ...array_fill(0, 4, ' '), $totalAmount, $movingBalance];
                fputcsv($file, $row);
            }
            fclose($file);
        };
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=General-Ledger.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        return response()->stream($callback, 200, $headers);
    }

    public function journalEntries()
    {
        $tr = Transaction::find(request('transaction_id'), ['tid']);
        $entries = Transaction::whereHas('account')->where('tid', $tr->tid)
            ->get()
            ->map(function($v) {
                $customer = @$v->customer->company ?: @$v->customer->name;
                $supplier = @$v->supplier->name ?: @$v->supplier->company;
                return [
                    'id' => $v->id,
                    'number' => $v->account->number,
                    'holder' => $v->account->holder,
                    'payee' => $customer ?: $supplier,
                    'debit' => +$v->debit,
                    'credit' => +$v->credit,
                ];
            });

        return response()->json($entries);
    }

    /** 
     * Custom Reporting Periods
     * */
    public function reportPeriods()
    {
        $today = date('d-m-Y');
        $yesterday = date('d-m-Y', strtotime('yesterday'));
        $startWeek = date('d-m-Y', strtotime('sunday last week'));
        $startLastWeek = date('d-m-Y', strtotime('sunday -2 week'));
        $endLastWeek = date('d-m-Y', strtotime('saturday -1 week'));
        $startMonth = date('01-m-Y');
        $startLastMonth = date('d-m-Y', strtotime('first day of last month'));
        $endLastMonth = date('d-m-Y', strtotime('last day of last month'));
        $currQuarter = ceil(date('n') / 3);
        $startCurrQuarter = date('d-m-Y', strtotime(date('Y') . '-' . (($currQuarter * 3) - 2) . '-1'));
        $endCurrQuarter = date('t-m-Y', strtotime(date('Y') . '-' . (($currQuarter * 3)) . '-1'));
        $prevQuarter = $currQuarter > 1? $currQuarter-1 : 0;
        if ($prevQuarter) {
            $startPrevQuarter = date('d-m-Y', strtotime(date('Y') . '-' . (($prevQuarter * 3) - 2) . '-1'));
            $endPrevQuarter = date('t-m-Y', strtotime(date('Y') . '-' . (($prevQuarter * 3)) . '-1'));
        }
        $startYear = date('01-01-Y');
        $endYear = date('t-12-Y');
        $startLastYear = date("d-m-Y", strtotime("last year January 1st"));
        $endLastYear = date("d-m-Y", strtotime("last year December 31st"));

        $periods = [
            'today' => [$today, $today],
            'yesterday' => [$yesterday, $yesterday],
            'thisWeek' => [$startWeek, $today],
            'lastWeek' => [$startLastWeek, $endLastWeek],
            'thisMonth' => [$startMonth, $today],
            'lastMonth' => [$startLastMonth, $endLastMonth],
            'thisQuarter' => [$startCurrQuarter, $endCurrQuarter],
            'lastQuarter' => $prevQuarter? [$startPrevQuarter, $endPrevQuarter] : [],
            'thisYear' => [$startYear, $endYear],
            'lastYear' => [$startLastYear, $endLastYear],
        ];
        return $periods;
    }
}
