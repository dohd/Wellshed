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
namespace App\Http\Controllers\Focus\deptor;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\deptor\DeptorRepository;
use App\Http\Requests\Focus\deptor\ManageDeptorRequest;
use Illuminate\Support\Facades\Storage;

/**
 * Class CreditorsTableController.
 */
class DeptorsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var CreditorRepository
     */
    protected $deptor;

    /**
     * contructor to initialize repository object
     * @param CreditorRepository $deptor ;
     */
    public function __construct(DeptorRepository $deptor)
    {
        $this->deptor = $deptor;
    }

    /**
     * This method return the data of the model
     * @param ManageCustomerRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageDeptorRequest $request)
    {
        //
        $core = $this->deptor->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
          
            ->addColumn('company', function ($deptor) {
               
                return '<a class="font-weight-bold" href="' . route('biller.customers.show', [$deptor->id]) . '">' . $deptor->company . '</a>' . $d;
            })
             ->addColumn('credit', function ($deptor) {
                return amountFormat($deptor->transactions->sum('credit'));
            }
            ) 
            ->addColumn('debit', function ($deptor) {
                return amountFormat($deptor->transactions->sum('debit'));
            }
            )

             ->addColumn('balance', function ($deptor) {
                return amountFormat($deptor->transactions->sum('debit')-$deptor->transactions->sum('credit') );
            }
            )
             ->addColumn('created_at', function ($deptor) {
                return dateFormat($deptor->created_at);
            })

            ->addColumn('actions', function ($deptor) {
                return'<a href="' . route('biller.deptors.index') . '?rel_type=3&rel_id=' . $deptor->id . '" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="List"><i class="fa fa-eye"></i></a> ';
            })
            ->make(true);
    }
}
