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

namespace App\Http\Controllers\Focus\creditnote;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\creditnote\CreditNoteRepository;
use Yajra\DataTables\Facades\DataTables;

class CreditNotesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var CreditNoteRepository
     */
    protected $creditnote;

    /**
     * contructor to initialize bill object
     * @param CreditNoteRepository $creditnote ;
     */
    public function __construct(CreditNoteRepository $creditnote)
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
        $prefixes = prefixesArray(['credit_note', 'debit_note', 'invoice'], $ins);

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($creditnote) use($prefixes) {
                return gen4tid($creditnote->is_debit ? "{$prefixes[1]}-" : "{$prefixes[0]}-", $creditnote->tid);
            })
            ->addColumn('customer', function ($creditnote) {
                $company = @$creditnote->customer->company;
                $name = @$creditnote->customer->name;
                if ($company && $name) return "{$company} - {$name}";
                return $company ?: $name;
            })
            ->addColumn('invoice_no', function ($creditnote) use($prefixes) {
                if ($creditnote->invoice)
                    return '<a class="font-weight-bold" href="' . route('biller.invoices.show', $creditnote->invoice) . '">' 
                        . gen4tid("{$prefixes[2]}-", $creditnote->invoice->tid) . '</a>';
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
                return '<a href="' . route('biller.creditnotes.print_creditnote', $creditnote) . '" target="_blank"  class="btn btn-purple round"><i class="fa fa-print"></i></a> '
                    . $creditnote->action_buttons;
            })
            ->make(true);
    }
}
