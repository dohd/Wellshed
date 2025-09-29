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
namespace App\Http\Controllers\Focus\projectstocktransfer;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\projectstocktransfer\ProductstocktransferRepository;
use App\Http\Requests\Focus\projectstocktransfer\ManageProductstocktransferRequest;

/**
 * Class PurchaseordersTableController.
 */
class ProjectstocktransfersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseorderRepository
     */
    protected $projectstocktransfer;

    /**
     * contructor to initialize repository object
     * @param PurchaseorderRepository $purchaseorder ;
     */
    public function __construct(ProductstocktransferRepository $projectstocktransfer)
    {
        $this->projectstocktransfer = $projectstocktransfer;
    }

    /**
     * This method return the data of the model
     * @param ManagePurchaseorderRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageProductstocktransferRequest $request)
    {


 
        $core = $this->projectstocktransfer->getForDataTable();

        return Datatables::of($core)
            ->addIndexColumn()
            ->addColumn('tid', function ($projectstocktransfer) {
                return '<a class="font-weight-bold" href="' . route('biller.projectstocktransfers.show', [$projectstocktransfer->id]) . '">' . $projectstocktransfer->tid . '</a>';
            })
            ->addColumn('trans_date', function ($projectstocktransfer) {
                return dateFormat($projectstocktransfer->transaction_date);
            })

            ->addColumn('client_id', function ($projectstocktransfer) {

                 return $projectstocktransfer->customer->company . ' <a class="font-weight-bold" href="' . route('biller.customers.show', [$projectstocktransfer->customer->id]) . '"><i class="ft-eye"></i></a>';

            })

            ->addColumn('branch_id', function ($projectstocktransfer) {
                return $projectstocktransfer->branch->name;
            })

             ->addColumn('credit', function ($projectstocktransfer) {
                return amountFormat($projectstocktransfer->credit);
            })
             ->addColumn('project_id', function ($projectstocktransfer) {
               return $projectstocktransfer->project->tid;
            })
            ->addColumn('created_at', function ($projectstocktransfer) {
                return dateFormat($projectstocktransfer->created_at);
            })
            
           
            ->addColumn('actions', function ($projectstocktransfer) {

                 
                   return $projectstocktransfer->action_buttons;
                

                //return $purchase->action_buttons;
            })->rawColumns(['tid', 'client_id','branch_id', 'project_id', 'actions'])
            ->make(true);
    }
}
