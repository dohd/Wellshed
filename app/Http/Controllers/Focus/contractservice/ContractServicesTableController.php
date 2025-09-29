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

namespace App\Http\Controllers\Focus\contractservice;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\contractservice\ContractServiceRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class BranchTableController.
 */
class ContractServicesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ContractServiceRepository
     */
    protected $contractservice;

    /**
     * contructor to initialize repository object
     * @param ContractServiceRepository $contractservice;
     */
    public function __construct(ContractServiceRepository $contractservice)
    {
        $this->contractservice = $contractservice;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->contractservice->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('client', function ($contractservice) {
                $customer = $contractservice->customer;
                $branch = $contractservice->branch;
                if ($customer && $branch)
                    return "{$customer->company} - {$branch->name}";
            })
            ->addColumn('contract', function ($contractservice) {
                $contract = $contractservice->contract;
                $schedule = $contractservice->task_schedule;
                if ($contract && $schedule)
                    return "{$contract->title} - {$schedule->title}";
            })
            ->addColumn('bill', function ($contractservice) {
                return amountFormat($contractservice->bill_ttl);
            })
            ->addColumn('unit', function ($contractservice) {
                return $contractservice->items->count();
            })
            ->addColumn('jobcard_no', function ($contractservice) {
                return 'Jc-' . $contractservice->jobcard_no;
            })
            ->addColumn('date', function ($contractservice) {
                return dateFormat($contractservice->date);
            })
            ->addColumn('actions', function ($contractservice) {
                return $contractservice->action_buttons;
            })
            ->make(true);
    }
}
