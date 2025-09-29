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
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class BranchTableController.
 */
class EquipmentsTableController extends Controller
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
        $core = $this->contractservice->getServiceReportItemsForDataTable();

        $sum_total = 0;
        foreach ($core as $item) {
            $equipment = $item->equipment;

            if ($equipment) {
                if (boolval($item->is_bill)) {
                    $sum_total += $equipment->service_rate;
                }
            }
        }
        
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('sum_total', function ($item) use($sum_total) {
                return numberFormat($sum_total);
            })
            ->addColumn('branch', function ($item) {
                $service = $item->contractservice;
                if ($service) {
                    $customer = $service->customer;
                    $branch = $service->branch;
                    if ($customer && $branch) return "{$branch->name}";
                }
            })
            ->addColumn('location', function ($item) {
                return $item->equipment->location;
            })
            ->addColumn('floor', function ($item) {
                return $item->equipment->floor;
            })
            ->addColumn('building', function ($item) {
                return $item->equipment->building;
            })
            ->addColumn('category', function ($item) {
                if (isset($item->equipment->category))
                return $item->equipment->category->name;
            })
            ->addColumn('make_type', function ($item) {
                return $item->equipment->make_type;
            })
            ->addColumn('model', function ($item) {
                return $item->equipment->model;
            })
            ->addColumn('capacity', function ($item) {
                return $item->equipment->capacity;
            })
            ->addColumn('equip_serial', function ($item) {
                return $item->equipment->equip_serial;
            })
            ->addColumn('unique_id', function ($item) {
                return $item->equipment->unique_id;
            })
            ->addColumn('machine_gas', function ($item) {
                return $item->equipment->machine_gas;
            })
            ->addColumn('service_rate', function ($item) {

                return boolval($item->is_bill) ? $item->equipment->service_rate : numberFormat(sprintf('%0.2f', 0.00));
            })
            ->addColumn('status', function ($item) {
                return Str::title($item->status);
            })
            ->addColumn('note', function ($item) {
                return $item->note;
            })
            ->addColumn('jobcard', function ($item) {
                $service = $item->contractservice;
                if ($service) return $service->jobcard_no;
            })
            ->addColumn('jobcard_date', function ($item) {
                $service = $item->contractservice;
                if ($service) return dateFormat($service->date);
            })
            ->make(true);
    }
}
