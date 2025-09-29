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

namespace App\Http\Controllers\Focus\contract;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\contract\ContractRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class BranchTableController.
 */
class ContractsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ContractRepository
     */
    protected $contract;

    /**
     * contructor to initialize repository object
     * @param ContractRepository $contract;
     */
    public function __construct(ContractRepository $contract)
    {

        $this->contract = $contract;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $query = $this->contract->getForDataTable();

        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($contract) {
                return $contract->tid;
            })
            ->addColumn('customer', function ($contract) {        
                if ($contract->customer) 
                return $contract->customer->company;
            })
            ->addColumn('amount', function ($contract) {
                return numberFormat($contract->amount);
            })
            ->addColumn('schedule', function ($contract) {
                return $contract->task_schedules->count();
            })
            ->addColumn('equipment', function ($contract) {
                return $contract->equipments()
                    ->whereHas('branch', fn($q) => $q->where('customer_id', $contract->customer_id))
                    ->count();
            })
            ->addColumn('start_date', function ($contract) {
                return dateFormat($contract->start_date);
            })
            ->addColumn('end_date', function ($contract) {
                return dateFormat($contract->end_date);
            })
            ->addColumn('actions', function ($contract) {
                return $contract->action_buttons;
            })
            ->make(true);
    }
}
