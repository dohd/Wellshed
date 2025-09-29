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
namespace App\Http\Controllers\Focus\creditor;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\creditor\CreditorRepository;
use App\Http\Requests\Focus\creditor\ManageCreditorRequest;
use Illuminate\Support\Facades\Storage;

/**
 * Class CreditorsTableController.
 */
class CreditorsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var CreditorRepository
     */
    protected $creditor;

    /**
     * contructor to initialize repository object
     * @param CreditorRepository $creditor ;
     */
    public function __construct(CreditorRepository $creditor)
    {
        $this->creditor = $creditor;
    }

    /**
     * This method return the data of the model
     * @param ManageCustomerRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageCreditorRequest $request)
    {
        //
        $core = $this->creditor->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
          
            ->addColumn('company', function ($creditor) {
               
                return '<a class="font-weight-bold" href="' . route('biller.suppliers.show', [$creditor->id]) . '">' . $creditor->company . '</a>' . $d;
            })
             ->addColumn('credit', function ($creditor) {
                return amountFormat($creditor->transactions->sum('credit'));
            }
            ) 
            ->addColumn('debit', function ($creditor) {
                return amountFormat($creditor->transactions->sum('debit'));
            }
            )

             ->addColumn('balance', function ($creditor) {
                return amountFormat($creditor->transactions->sum('credit') -$creditor->transactions->sum('debit') );
            }
            )
             ->addColumn('created_at', function ($creditor) {
                return dateFormat($creditor->created_at);
            })

            ->addColumn('actions', function ($creditor) {
                return'<a href="' . route('biller.creditors.index') . '?rel_type=2&rel_id=' . $creditor->id . '" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="List"><i class="fa fa-eye"></i></a> ';
            })
            ->make(true);
    }
}
