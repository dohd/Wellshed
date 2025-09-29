<?php
/* Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 **
 * Rose Business Suite - Accounting, CRM and POS Software
 
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\supplier_creditnote;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\supplier_creditnote\SupplierCreditNoteRepository;
use Yajra\DataTables\Facades\DataTables;

class SupplierCreditNotesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var SupplierCreditNoteRepository
     */
    protected $creditnote;

    /**
     * contructor to initialize bill object
     * @param SupplierCreditNoteRepository $creditnote ;
     */
    public function __construct(SupplierCreditNoteRepository $creditnote)
    {
        $this->creditnote = $creditnote;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->creditnote->getForDataTable();

        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['credit_note', 'debit_note', 'bill','goods_received'], $ins);

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($creditnote) use($prefixes) {
                return gen4tid($creditnote->is_debit ? "{$prefixes[1]}-" : "SCN-", $creditnote->tid);
            })
            ->addColumn('supplier', function ($creditnote) {
                $company = @$creditnote->supplier->company;
                $name = @$creditnote->supplier->name;
                if ($company && $name) return "{$company} - {$name}";
                return $company ?: $name;
            })
            ->addColumn('bill_no', function ($creditnote) use($prefixes) {
                if ($creditnote->bill)
                    return '<a class="font-weight-bold" href="' . route('biller.utility_bills.show', $creditnote->bill) . '">' 
                        . gen4tid("{$prefixes[2]}-", $creditnote->bill->tid) . '</a>';
                elseif ($creditnote->grn)
                    return '<a class="font-weight-bold" href="' . route('biller.goodsreceivenote.show', $creditnote->grn) . '">' 
                        . gen4tid("{$prefixes[3]}-", $creditnote->grn->tid) . '</a>';
            })
            ->editColumn('subtotal', function ($creditnote) {
                return numberFormat($creditnote->subtotal);
            })
            ->editColumn('tax', function ($creditnote) {
                return numberFormat($creditnote->tax);
            })
            ->editColumn('total', function ($creditnote) {
                return numberFormat($creditnote->total);
            })
            ->addColumn('date', function ($creditnote) {
                return dateFormat($creditnote->date);
            })
            ->addColumn('actions', function ($creditnote) {
                return '<a href="' . route('biller.supplier_creditnotes.print_creditnote', $creditnote) . '" target="_blank"  class="btn btn-purple round"><i class="fa fa-print"></i></a> '
                    . $creditnote->action_buttons;
            })
            ->make(true);
    }
}
