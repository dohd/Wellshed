<?php

namespace App\Http\Controllers\Focus\supplier;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\supplier\SupplierRepository;

/**
 * Class SuppliersTableController.
 */
class SuppliersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var SupplierRepository
     */
    protected $supplier;
    protected $balance = 0;

    /**
     * contructor to initialize repository object
     * @param SupplierRepository $supplier ;
     */
    public function __construct(SupplierRepository $supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * This method return the data of the model
     *
     * @return mixed
     */
    public function __invoke()
    {
        if (request('is_transaction')) return $this->invoke_transaction();
        if (request('is_bill')) return $this->invoke_bill();
        if (request('is_statement')) return $this->invoke_statement();
            
        $core = $this->supplier->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('name', function ($supplier) {
                return '<a class="font-weight-bold" href="'.route('biller.suppliers.show', $supplier).'">'. gen4tid('SRM-', $supplier->id) . " | " . $supplier->name . '</a>';
            })
            ->make(true);
    }

    // Statement On Account
    public function invoke_transaction()
    {
        $core = $this->supplier->getTransactionsForDataTable();
        $core = $core->sortBy('tr_date');

        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('date', function ($tr) {
            $date = dateFormat($tr->tr_date);
            $sort_id = strtotime($date);
            return "<span sort_id='{$sort_id}'>{$date}</span>";
        })
        ->addColumn('type', function ($tr) {
            $tr_type = $tr->tr_type;
            if ($tr->bill) $tr_type = '<a href="'. route('biller.utility_bills.show', $tr->bill) .'">'. $tr_type .'</a>';
            if ($tr->bill_payment) $tr_type = '<a href="'. route('biller.billpayments.show', $tr->bill_payment) .'">'. $tr_type .'</a>';
            if ($tr->manualjournal) $tr_type = '<a href="'. route('biller.journals.show', $tr->manualjournal) .'">'. $tr_type .'</a>';
            return $tr_type;
        })
        ->addColumn('note', function ($tr) {
            $tid = '';
            if ($tr->bill) $tid = gen4tid('BILL-', $tr->bill->tid);
            if ($tr->bill_payment) $tid = gen4tid('RMT-', $tr->bill_payment->tid);
            if ($tr->manualjournal) $tid = gen4tid('JNL-', $tr->manualjournal->tid);

            return "({$tid}) <br> {$tr->note}";
        })
        ->addColumn('bill_amount', function ($tr) {
            return numberFormat($tr->credit);
        })
        ->addColumn('amount_paid', function ($tr) {
            return numberFormat($tr->debit);
        })
        ->addColumn('account_balance', function ($tr) {
            if ($tr->debit > 0) $this->balance -= $tr->debit;
            elseif ($tr->credit > 0) $this->balance += $tr->credit;

            return numberFormat($this->balance);
        })
        ->make(true);
    }

    // Bill List
    public function invoke_bill()
    {
        $core = $this->supplier->getBillsForDataTable();
        
        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('date', function ($bill) {
            return dateFormat($bill->date);
        })
        ->addColumn('status', function ($bill) {
            return $bill->status;
        })
        ->addColumn('note', function ($bill) {
            return gen4tid('BILL-', $bill->tid) . ' - ' . $bill->note;
        })
        ->addColumn('amount', function ($bill) {
            return numberFormat($bill->total);
        })
        ->addColumn('paid', function ($bill) {
            return numberFormat($bill->amount_paid);
        })
        ->make(true);
    }

    // Statement On Bill
    public function invoke_statement()
    {
        $core = $this->supplier->getStatementForDataTable();
        
        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('date', function ($statement) {
            return dateFormat($statement->date);
        })
        ->addColumn('type', function ($statement) {
            $record = $statement->type;
            switch ($record) {
                case 'bill': 
                    $record = '<a href="'. route('biller.utility_bills.show', $statement->bill_id) .'">'. $record .'</a>';
                    break;
                case 'payment': 
                    // $type = '<a href="'. route('biller.invoices.show', $statement->invoice_id) .'">'. $type .'</a>';
                    break; 
            }
            
            return $record;
        })
        ->addColumn('note', function ($statement) {
            return $statement->note;
        })
        ->addColumn('bill_amount', function ($statement) {
            return numberFormat($statement->credit);
        })
        ->addColumn('amount_paid', function ($statement) {
            return numberFormat($statement->debit);
        })
        ->addColumn('bill_balance', function ($statement) {
            if ($statement->type == 'bill') 
                $this->balance = $statement->credit;
            else $this->balance -= $statement->debit;

            return numberFormat($this->balance);
        })
        ->make(true);
    }
}
