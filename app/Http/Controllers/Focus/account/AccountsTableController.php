<?php

namespace App\Http\Controllers\Focus\account;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\account\AccountRepository;
use App\Http\Requests\Focus\account\ManageAccountRequest;
use App\Models\transaction\Transaction;

/**
 * Class AccountsTableController.
 */
class AccountsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $account ;
     */
    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param ManageAccountRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageAccountRequest $request)
    {   
        // account balances
        $sum_groups = Transaction::withoutGlobalScopes()
        ->selectRaw(
            'rose_transactions.account_id, 
            rose_accounts.account_type, 
            SUM(rose_transactions.debit) debit, 
            SUM(rose_transactions.credit) credit, 
            IF(rose_accounts.account_type = "Asset" OR rose_accounts.account_type = "Expense", 
               SUM(rose_transactions.debit - rose_transactions.credit), 
               SUM(rose_transactions.credit - rose_transactions.debit)
            ) balance'
        )
        ->join('accounts', 'transactions.account_id', '=', 'accounts.id') // Join with accounts table
        ->where('transactions.ins', auth()->user()->ins)
        ->groupBy('transactions.account_id', 'accounts.account_type') // Group by account_id and account_type
        ->get()
        ->reduce(function($prev, $curr) {
            $prev[$curr->account_id] = $curr;
            return $prev;
        },[]);
        
        $core = $this->repository->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id', 'number', 'holder'])
            ->addIndexColumn()
            ->addColumn('debit', function ($account) use($sum_groups) {
                return numberFormat(@$sum_groups[$account->id]->debit);
            })
            ->addColumn('credit', function ($account) use($sum_groups) {
                return numberFormat(@$sum_groups[$account->id]->credit);
            })
            ->addColumn('balance', function ($account) use($sum_groups) {
                return numberFormat(@$sum_groups[$account->id]->balance);
            })
            ->editColumn('account_type', function ($account) use($sum_groups) {
                $accountType = @$account->accountType->name;
                if (!$account->account_type_detail && @$sum_groups[$account->id]->balance)
                    $accountType .= '<br><span class="text-danger">Missing Detail Type</span>';
                return  $accountType;
            })
            ->addColumn('system_type', function ($account) {
                $ledger_status = $account->is_parent && $account->is_parent? 'sub-ledger' : 'ledger';
                $system_type = $account->system? "default-{$ledger_status}" : "custom-{$ledger_status}";
                return $system_type;
            })
            ->addColumn('actions', function ($account) {
                return $account->action_buttons;
            })
            ->make(true);
    }
}
