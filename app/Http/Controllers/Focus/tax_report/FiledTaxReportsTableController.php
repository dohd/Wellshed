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
namespace App\Http\Controllers\Focus\tax_report;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\tax_report\TaxReportRepository;
use Illuminate\Support\Str;
use Request;
use Yajra\DataTables\Facades\DataTables;


class FiledTaxReportsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaxReportRepository
     */
    protected $repository;

    // sale variables
    protected $invoice;
    protected $credit_note;
    protected $customer;

    // purchase variables
    protected $purchase;
    protected $bill;
    protected $debit_note;
    protected $supplier;

    /**
     * contructor to initialize repository object
     * @param TaxReportRepository $repository ;
     */
    public function __construct(TaxReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        if (request('is_sale')) return $this->sale_data();
        if (request('is_purchase')) return $this->purchase_data();
    }

    /**
     * Sales data table
     * */
    public function sale_data()
    {
        $core = $this->repository->getForSalesDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('pin', function ($item) {
                $pin = '';
                if ($item->invoice) {
                    $invoice = $item->invoice;
                    $this->invoice = $invoice;
                    $this->credit_note = null;
                    $this->customer = $invoice->customer;
                } elseif ($item->credit_note) {
                    $credit_note = $item->credit_note;
                    $this->credit_note = $credit_note;
                    $this->invoice = null;
                    $this->customer = $credit_note->customer;
                }

                if ($this->customer) $pin .= $this->customer->taxid;

                return $pin;
            })
            ->addColumn('customer', function ($item) {
                if ($this->customer)
                return Str::limit($this->customer->company,47);
            })
            ->addColumn('etr_code', function ($item) {
                return 0;
            })
            ->addColumn('invoice_date', function ($item) {
                $date = '';
                if ($this->credit_note) $date = $this->credit_note->date;
                elseif ($this->invoice) $date = $this->invoice->invoicedate;
                if ($date) $date = dateFormat($date, 'd/m/Y');

                return $date;
            })
            ->addColumn('cu_invoice_no', function ($item) {

                $cuInvoiceNo = '';

                if ($this->credit_note) $cuInvoiceNo = $this->credit_note->cu_invoice_no ?? '';
                elseif ($this->invoice) $cuInvoiceNo = $this->invoice->cu_invoice_no ?? '';

                if (!empty($cuInvoiceNo)){
                    return "|" . $cuInvoiceNo;
                }

                return $cuInvoiceNo;
            })
            ->addColumn('note', function ($item) {
                if ($this->credit_note) return 'Credit Note';
                elseif ($this->invoice) return $this->invoice->notes;
            })
            ->addColumn('subtotal', function ($item) {
                $subtotal = 0;
                if ($this->credit_note) $subtotal = -1* $this->credit_note->subtotal;
                elseif ($this->invoice) $subtotal = $this->invoice->subtotal;

                return numberFormat($subtotal);
            })
            ->addColumn('empty_col', function ($item) {
                return '';
            })
            ->addColumn('invoice_no', function ($item) {

                if ($this->credit_note) return gen4tid('CN-', $this->credit_note->tid);
                elseif ($this->invoice) return gen4tid('INV-', $this->invoice->tid);
            })
            ->addColumn('cn_invoice_date', function ($item) {
                $cn_invoice_date = '';
                if ($this->credit_note) {
                    $invoice = $this->credit_note->invoice;
                    if ($invoice) $cn_invoice_date .= dateFormat($invoice->invoicedate, 'd/m/Y');
                }
                return $cn_invoice_date;
            })
            ->make(true);
    }

    /**
     * Purchase data table
     * */
    public function purchase_data()
    {
        $core = $this->repository->getForPurchasesDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('pin', function ($item) {
                $pin = '';
                $bill = $item->bill;
                if ($bill && $bill->document_type) {
                    if ($bill->document_type == 'direct_purchase' && $bill->purchase) {
                        $purchase = $bill->purchase;
                        $this->purchase = $purchase;
                        $pin .= $purchase->supplier_taxid;
                    } elseif ($bill->supplier) {
                        $this->purchase = null;
                        $pin .= $bill->supplier->taxid;
                    }
                    $this->bill = $bill;
                    $this->supplier = $bill->supplier;
                    $this->debit_note = null;
                } elseif ($item->debit_note) {
                    $debit_note = $item->debit_note;
                    $pin .= $debit_note->supplier->taxid;
                    $this->debit_note = $debit_note;
                    $this->supplier = $debit_note->supplier;
                    $this->bill = null;
                    $this->purchase = null;
                }

                return $pin;
            })
            ->addColumn('supplier', function ($item) {
                $suppliername = '';
                if ($this->purchase) $suppliername .= $this->purchase->suppliername;
                elseif ($this->supplier) $suppliername .= $this->supplier->name;
                // limit to 50 chars as per KRA portal
                return  Str::limit($suppliername,47);
            })
            ->addColumn('invoice_date', function ($item) {
                $date = '';
                if ($this->debit_note) $date = $this->debit_note->date;
                elseif ($this->bill) $date = $this->bill->date;
                if ($date) $date = dateFormat($date, 'd/m/Y');

                return $date;
            })
            ->addColumn('invoice_no', function ($item) {
                $tid = '';
                if ($this->debit_note) $tid = $this->debit_note->tid;
                elseif ($item->bill) $tid = $item->bill->reference;
                if ($tid && $tid[0] != 0 && is_numeric($tid[0])) return "'" . $tid;
                return $tid;
            })
            ->addColumn('note', function ($item) {
                $note = '';
                if ($this->bill && $this->bill->tax_rate == 8) $note = 'Fuel';
                elseif ($this->bill) $note = 'Goods';
                elseif ($this->debit_note) $note = 'Credit Note';

                return $note;
            })
            ->addColumn('subtotal', function ($item) {
                $subtotal = 0;
                if ($this->debit_note) $subtotal = $this->debit_note->subtotal;
                elseif ($this->bill) $subtotal = $this->bill->subtotal;

                return numberFormat($subtotal);
            })
            ->addColumn('empty_col', function ($item) {
                return '';
            })
            ->addColumn('source', function ($item) {
                return 'Local';
            })
            ->make(true);
    }
}
