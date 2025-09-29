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

namespace App\Http\Controllers\Focus\withholding;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\withholding\WithholdingRepository;
use App\Http\Requests\Focus\withholding\ManageWithholdingRequest;

/**
 * Class BanksTableController.
 */
class WithholdingsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var WithholdingRepository
     */
    protected $withholding;

    /**
     * contructor to initialize repository object
     * @param WithholdingRepository $withholding ;
     */
    public function __construct(WithholdingRepository $withholding)
    {
        $this->withholding = $withholding;
    }

    /**
     * This method return the data of the model
     * @param ManageBankRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageWithholdingRequest $request)
    {
        $core = $this->withholding->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($withholding) {
                return gen4tid('WH-', $withholding->tid);
            })
            ->addColumn('customer', function ($withholding) {
                return '<a href="'. route('biller.customers.show', $withholding->customer) .'">'. ($withholding->customer->company ?: $withholding->customer->name) .'</a>';
            })
            ->addColumn('reference', function ($withholding) {
                return $withholding->reference;
            })
            ->addColumn('cert_date', function ($withholding) {
                return dateFormat($withholding->cert_date);
            })
            ->addColumn('amount', function ($withholding) {
                return numberFormat($withholding->amount);
            })
            ->addColumn('allocate_ttl', function ($withholding) {
                return numberFormat($withholding->allocate_ttl);
            })
            ->addColumn('invoice_tid', function ($withholding) {
                $items = $withholding->items;
                if ($items->count()) {
                    $invoice_tids = array();
                    foreach ($items as $item) {
                        if ($item->invoice) $invoice_tids[] = '<a href="'. route('biller.invoices.show', $item->invoice) .'">'. gen4tid('Inv-', $item->invoice->tid) .'</a>';
                    }
                    return implode(', ', $invoice_tids);
                }
            })
            ->addColumn('actions', function ($withholding) {
                return $withholding->action_buttons;
            })
            ->make(true);
    }
}
