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
namespace App\Http\Controllers\Focus\invoice;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\invoice\InvoiceRepository;
use App\Http\Requests\Focus\invoice\ManageInvoiceRequest;

/**
 * Class InvoicesTableController.
 */
class PaymentsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var InvoiceRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param InvoiceRepository $repository ;
     */
    public function __construct(InvoiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param ManageInvoiceRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageInvoiceRequest $request)
    {
        $core = $this->repository->getPaymentsForDataTable();

        // aggregate
        $amount_total = $core->sum('amount');
        $unallocated_total = $amount_total - $core->sum('allocate_ttl');
        $aggregate = [
            'amount_total' => numberFormat($amount_total),
            'unallocated_total' => numberFormat($unallocated_total),
        ];

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('tid', function ($payment) {
                return gen4tid('PMT-', $payment->tid);
            })
            ->addColumn('account', function ($payment) {
                if ($payment->account)
                return $payment->account->holder;
            })        
            ->addColumn('date', function ($payment) {
                return dateFormat($payment->date);
            })
            ->addColumn('amount', function ($payment) {
                return numberFormat($payment->amount);
            })
            ->addColumn('unallocated', function ($payment) {
                return numberFormat($payment->amount - $payment->allocate_ttl);
            })
            ->addColumn('invoice_tid', function ($payment) {
                if (!$payment->items->count()) return;
                $invoice_tids = array();
                foreach ($payment->items as $item) {
                    if ($item->invoice) $invoice_tids[] = gen4tid('Inv-', $item->invoice->tid);
                }
                return implode(', ', $invoice_tids);
            })
            ->addColumn('aggregate', function ($payment) use($aggregate) {
                return $aggregate;
            })
            ->addColumn('actions', function ($payment) {
                return $this->action_buttons($payment);
            })
            ->make(true);
    }

    // action buttons
    public function action_buttons($payment)
    {
        $print = ' <a href="' . route('biller.invoices.print_payment', $payment) . '" target="_blank"  class="btn btn-purple round"><i class="fa fa-print"></i></a> ';
        $edit = ' <a href="' . route('biller.invoices.edit_payment', $payment) . '" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="fa fa-pencil"></i></a> ';
        $view = ' <a href="' . route('biller.invoices.show_payment', $payment) . '" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="View"><i  class="fa fa-eye"></i></a> ';
        $delete = '<a href="' . route('biller.invoices.delete_payment', $payment) . '" 
                class="btn btn-danger round" data-method="post"
                data-trans-button-cancel="' . trans('buttons.general.cancel') . '"
                data-trans-button-confirm="' . trans('buttons.general.crud.delete') . '"
                data-trans-title="' . trans('strings.backend.general.are_you_sure') . '" data-toggle="tooltip" data-placement="top" title="Delete"
            >
                <i  class="fa fa-trash"></i>
            </a>';

        return $print . $view . $edit . $delete;
    }
}
