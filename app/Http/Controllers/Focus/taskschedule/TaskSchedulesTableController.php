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

namespace App\Http\Controllers\Focus\taskschedule;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\taskschedule\TaskScheduleRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class BranchTableController.
 */
class TaskSchedulesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaskScheduleRepository
     */
    protected $schedule;

    protected $service_status;

    /**
     * contructor to initialize repository object
     * @param TaskScheduleRepository $schedule;
     */
    public function __construct(TaskScheduleRepository $schedule)
    {
        $this->schedule = $schedule;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $query = $this->schedule->getForDataTable();
        $query->with(['contract_equipments', 'contract_service_items', 'equipments.branch']);

        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('contract', function ($schedule) {   
                $contract_name = '';
                if ($schedule->contract) {
                    $contract = $schedule->contract;
                    if ($contract->customer) $contract_name .= $contract->customer->company;
                    $contract_name = '<a href="'. route('biller.contracts.show', $contract).'">'.$contract_name.'</a>';
                }

                return $contract_name;
            })
            ->addColumn('loaded', function ($schedule) {
                $scheduleEquipIds = $schedule->contract_equipments->pluck('equipment_id')->toArray();
                $servicedEquipIds = $schedule->contract_service_items->whereIn('equipment_id', $scheduleEquipIds)
                    ->pluck('equipment_id')
                    ->toArray();

                // count
                $numScheduleUnits = count($scheduleEquipIds);
                $numServicedUnits = count($servicedEquipIds);
                $numUnservicedUnits = count(array_diff($scheduleEquipIds, $servicedEquipIds));

                // service status
                if ($numServicedUnits) {
                    if ($numServicedUnits >= $numScheduleUnits) $this->service_status = 'complete';
                    else $this->service_status = 'partial';
                } else $this->service_status = 'unserviced';
                    
                $params = [
                    'contract_id' => @$schedule->contract->id,
                    'customer_id' => @$schedule->contract->customer_id,
                    'schedule_id' => $schedule->id,
                    'is_serviced' => 0,
                ];
                $unservicedEquipLink = '<a href="'. route('biller.equipments.index', $params) .'">unserviced:</a>';

                return "{$unservicedEquipLink} <b>{$numUnservicedUnits}/{$numScheduleUnits}</b> <br> serviced: <b>{$numServicedUnits}/{$numScheduleUnits}</b>";
            })
            ->addColumn('total_rate', function ($schedule) {
                $customer_id = @$schedule->contract->customer_id;
                $totalSeviceRate = $schedule->equipments()
                    ->whereHas('branch', fn($q) => $q->where('customer_id', $customer_id))
                    ->sum('service_rate');
                return numberFormat($totalSeviceRate);
            })
            ->addColumn('total_charged', function ($schedule) {
                $customer_id = @$schedule->contract->customer_id;
                $servicedEquipIds = $schedule->contract_service_items->pluck('equipment_id')->toArray();
                $totalCharged  = $schedule->equipments()
                    ->whereIn('equipments.id', $servicedEquipIds)
                    ->whereHas('branch', fn($q) => $q->where('customer_id', $customer_id))
                    ->sum('service_rate');

                return numberFormat($totalCharged);
            })
            ->addColumn('start_date', function ($schedule) {
                return dateFormat($schedule->start_date);
            })
            ->addColumn('actual_startdate', function ($schedule) {
                return dateFormat($schedule->actual_startdate);
            })
            ->addColumn('service_status', function ($schedule) {
                return $this->service_status;
            })
            ->addColumn('actions', function ($schedule) {
                $params = [
                    'schedule_id' => $schedule->id, 
                    'customer_id' => ''
                ];
                if ($schedule->contract) $params['customer_id'] = $schedule->contract->customer_id;
                 
                return $schedule->action_buttons 
                    . ' <a class="btn btn-purple round" href="'. route('biller.equipments.index', $params) .'" title="equipments"><i class="fa fa-list"></i></a> '; 
            })
            ->make(true);
    }
}
