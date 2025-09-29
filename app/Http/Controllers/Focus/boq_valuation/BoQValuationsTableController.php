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
namespace App\Http\Controllers\Focus\boq_valuation;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Repositories\Focus\boq_valuation\BoQValuationRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class BoQValuationsTableController.
 */
class BoQValuationsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var BoQValuationRepository
     */
    protected $boq_valuation;

    /**
     * contructor to initialize repository object
     * @param BoQValuationRepository $boq_valuation ;
     */
    public function __construct(BoQValuationRepository $boq_valuation)
    {
        $this->boq_valuation = $boq_valuation;
    }

    /**
     * This method return the data of the model
     * @param Manageboq_valuationRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->boq_valuation->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()   
            ->addColumn('row_check', function ($boq_valuation) {
                if ($boq_valuation->invoice)
                    return  '<input checked disabled type="checkbox"  class="row-check ml-1" href="'. route('biller.invoices.filter_invoice_quotes', ['boq_valuation_id' => $boq_valuation->id]) .'">';
                return  '<input type="checkbox"  class="row-check ml-1" href="'. route('biller.invoices.filter_invoice_quotes', ['boq_valuation_id' => $boq_valuation->id]) .'">';
            })
            ->addColumn('invoice_tid', function ($boq_valuation) {
                $invoice = $boq_valuation->invoice;
                if (!$invoice) return;
                return '<a href="'. route('biller.invoices.show', $invoice) .'">'. gen4tid('INV-', $invoice->tid) .'</a>';
            })
            ->editColumn('tid', function ($boq_valuation) {
                return '<a href="'. route('biller.boq_valuations.show', $boq_valuation) .'">'. gen4tid('BV-', $boq_valuation->tid) .'</a>';
            })
            ->addColumn('boq_tid', function ($boq_valuation) {
                $boq = $boq_valuation->boq;
                if (!$boq) return;
                return '<a href="'. route('biller.boqs.show', $boq) .'">'. gen4tid('BoQ-', $boq->tid) .'</a>';
            })
            ->addColumn('quote_amount', function ($boq_valuation) {
                $boq = $boq_valuation->boq;
                if (!$boq) return;
                return numberFormat($boq->total_boq_amount);
            })
            ->editColumn('date', function ($boq_valuation) {
                return dateFormat($boq_valuation->date);
            }) 
            ->addColumn('customer', function ($boq_valuation) {
                $lead = optional($boq_valuation->boq->lead);
                $customer = optional($lead->customer);
                $branch = optional($lead->branch);
            
                $name = $customer->company ?: $customer->name;
                return $name ? $name . ($branch->name ? " - {$branch->name}" : '') : '';
            })
            ->editColumn('subtotal', function ($boq_valuation) {
                return numberFormat($boq_valuation->valued_subtotal);
            })
            ->editColumn('balance', function ($boq_valuation) {
                return numberFormat($boq_valuation->balance);
            })
            ->addColumn('actions', function ($boq_valuation) {
                return $boq_valuation->action_buttons;
            })
            ->make(true);
    }
}
