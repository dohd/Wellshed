<?php

namespace App\Http\Controllers\Focus\transaction;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\transaction\TransactionRepository;
use App\Http\Requests\Focus\transaction\ManageTransactionRequest;

/**
 * Class TransactionsTableController.
 */
class TransactionsTableController extends Controller
{
    protected $project;
    /**
     * variable to store the repository object
     * @var TransactionRepository
     */
    protected $transaction;


    /**
     * list of transaction groups indicating error in double entry
     * 
     * @var array $balance_group
     */
    protected $balance_groups;
    protected $debitTotal = 0;
    protected $creditTotal = 0;

    /**
     * contructor to initialize repository object
     * @param TransactionRepository $transaction ;
     */
    public function __construct(TransactionRepository $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * This method return the data of the model
     * @param ManageTransactionRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        $query = $this->transaction->getForDataTable()
        ->with([
            'account.currency',
            'account.accountType',
            'purchase_item',
            'category',
            'invoice.products.quote',
            'bill.grn_items',
            'bill.purchase',
            'invoice',
            'customer',
            'supplier',
        ]);
        
        // balance group
        $query_1 = clone $query;
        $result = $query_1->get();
        $this->balance_groups = $result->groupBy('tid')->reduce(function ($init, $v) {
            if ($v->where('fx_curr_rate','>', 0)->count()) $balance = round($v->sum('fx_credit') - $v->sum('fx_debit'));
            else $balance = round($v->sum('credit') - $v->sum('debit'));
            if ($balance) $init[] = (object) ['tid' => $v->first()->tid, 'balance' => $balance];
            return $init;
        }, []);

        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            // checkbox input for reclassifying transactions 
            ->addColumn('row_check', function ($tr) {
                return '<input type="checkbox" class="check-row" data-id="'. $tr->id .'">';        
            })
            ->editColumn('tid', function ($tr) {
                return gen4tid('Tr-', $tr->tid);                
            })
            ->addColumn('tr_type', function ($tr) {
                $tax_tr_type = $this->vatTrans('tr_type', $tr);
                if ($tax_tr_type) return $tax_tr_type;
                
                return @$tr->category->name;
            })
            ->addColumn('reference', function ($tr) {
                $tax_tr = $this->vatTrans('reference', $tr);
                if ($tax_tr) return $tax_tr;
                if ($tr->account) return $tr->account->holder;
            })
            ->addColumn('vat_rate', function ($tr) {
                return $this->vatTrans('vat_rate', $tr);                
            })
            ->addColumn('vat_amount', function ($tr) {
                return $this->vatTrans('vat_amount', $tr);
            })
            ->addColumn('payer', function ($tr) {
                if (in_array(request('system'), ['wip', 'cog'])) {
                    $this->project = $this->projectTrans($tr);
                }

                $customer = $tr->customer;
                if ($this->project) $customer = $customer ?: $this->project->customer;
                if ($tr->invoice) $customer = $customer ?: @$tr->invoice->customer;
                if ($customer) $customer = @$customer->company ?: @$customer->name;

                return $customer;
            })
            ->addColumn('payee', function ($tr) {
                $supplier = $tr->supplier;
                if ($tr->bill) $supplier = $supplier ?: $tr->bill->supplier;
                if ($supplier) $supplier = @$supplier->name ?: @$supplier->company;

                return $supplier;
            })
            ->addColumn('project_id', function ($tr) {
                return @$this->project->id;
            })
            ->addColumn('project', function ($tr) {
                $project = $this->project;
                $projectName = $project? gen4tid('Prj-', $project->tid).': '.$project->name : '';
                return $projectName;
            })
            ->addColumn('fx_amount', function ($tr) {
                $isFx = +$tr->fx_curr_rate && +$tr->fx_curr_rate != 1;
                $amount = $tr->fx_debit > 0? $tr->fx_debit : ($tr->fx_credit > 0? $tr->fx_credit : 0);
                return $isFx? numberFormat($amount) : 0;
            })
            ->editColumn('fx_curr_rate', function ($tr) {
                return numberFormat(+$tr->fx_curr_rate ?: 1);
            })
            ->addColumn('amount', function ($tr) {
                $amount = $tr->debit > 0? $tr->debit : ($tr->credit > 0? $tr->credit : 0);
                return numberFormat($amount);
            })
            ->editColumn('debit', function ($tr) {
                $debit = $tr->debit;
                $isFx = +$tr->fx_curr_rate && +$tr->fx_curr_rate != 1;
                if ($isFx) $debit = $tr->fx_debit;
                $this->debitTotal += round($debit,2);
                return numberFormat($debit);
            })
            ->editColumn('credit', function ($tr) {
                $credit = $tr->credit;
                $isFx = +$tr->fx_curr_rate && +$tr->fx_curr_rate != 1;
                if ($isFx) $credit = $tr->fx_credit;
                $this->creditTotal += round($credit,2);
                return numberFormat($credit);
            })
            ->addColumn('balance', function ($tr) {
                $balance = 0;
                foreach($this->balance_groups as $group) {
                    if ($group->tid == $tr->tid) {
                        $balance = $group->balance;
                        break;
                    }
                }
                return numberFormat($balance);
            })
            ->editColumn('tr_date', function ($tr) {
                return '<a href="#'. $tr->id .'">'.dateFormat($tr->tr_date).'</a>';
            })
            ->addColumn('aggregate', function ($tr) {
                $accountType = @$tr->account->accountType;
                if (in_array($accountType, ['Asset', 'Expense'])) $balance = round($this->debitTotal-$this->creditTotal,2);
                else $balance = round($this->creditTotal-$this->debitTotal,2);
                return [
                    'debit' => numberFormat($this->debitTotal), 
                    'credit' => numberFormat($this->creditTotal),
                    'balance' => numberFormat($balance),
                ];
            })
            ->addColumn('actions', function ($tr) {
                return $tr->action_buttons;
            })
            ->make(true);
    }

    // Project Transactions
    public function projectTrans($tr)
    {
        $project = @$tr->purchase_item->project;
        if ($project) return $project;

        if ($tr->grn) {
            $grn_item = $tr->grn->items->whereNotNull('itemproject_id')->first();
            // $grn_item = $tr->grn->items()->whereHas('project')->first();
            if (@$grn_item->project) return $grn_item->project;
        }
        if ($tr->bill) {
            if (@$tr->bill->purchase) {
                $purchase_item = $tr->bill->purchase->items->whereNotNull('itemproject_id')->first();
                // $purchase_item = $tr->bill->purchase->items()->whereHas('project')->first();
                if (@$purchase_item->project) return $purchase_item->project;
            }
            if (count($tr->bill->grn_items)) {
                $grn_item = $tr->bill->grn_items->whereNotNull('itemproject_id')->first();
                // $grn_item = $tr->bill->grn_items()->whereHas('project')->first();
                if (@$grn_item->project) return $grn_item->project;
            }
        }
        if ($tr->invoice) {
            foreach ($tr->invoice->products as $invoiceProduct) {
                $project = @$invoiceProduct->quote->project;
                if ($project) return $project;
            }
        }
        return;
    }

    // VAT Transaction
    public function vatTrans($col='', $tr)
    {
        if (request('system') == 'tax') {
            switch ($col) {
                case 'reference':
                    if ($tr->invoice) 
                        return $tr->invoice->customer->taxid . ' : ' . $tr->invoice->customer->company;
                    if ($tr->bill)
                        return $tr->bill->supplier->taxid . ' : ' . $tr->bill->supplier->company;
                case 'tr_type':
                    if ($tr->invoice) return 'Sale';
                    if ($tr->bill) return 'Purchase';
                case 'vat_rate':
                    if ($tr->invoice) return $tr->invoice->tax_id;
                    if ($tr->bill) return $tr->bill->tax;
                case 'vat_amount':
                    if ($tr->invoice) return numberFormat($tr->invoice->tax);
                    if ($tr->bill) return numberFormat($tr->bill->grandtax);
            }
        }
    }    
}