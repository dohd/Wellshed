<?php

namespace App\Http\Controllers\Focus\environmentalTracking;

use App\Http\Controllers\Controller;
use App\Models\environmentalTracking\EnvironmentalTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class EnvironmentalTrackingTableController extends Controller
{
    const MODEL = EnvironmentalTracking::class;

    public function __invoke()
    {
        //
        // $core = $this->term->getForDataTable();
        // $core = EnvironmentalTracking::where('date', request('date'))
        //     ->where('pdca_cycle', request('pdca_cycle'))
        //     ->get();

        $core = EnvironmentalTracking::when(request('client_filter'), function ($q) {
                $q->where('customer_id', request('client_filter'));
            })
            ->when(request('project_filter'), function ($q) {
                $q->where('project_id', request('project_filter'));
            })
            ->when(request('status_filter'), function ($q) {
                $q->where('status', request('status_filter'));
            })
            ->when(request('month_filter'), function ($q) {
                $q->whereMonth('date', request('month_filter'));
            })
            ->get();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('date', function ($environmentalTracking) {
                return dateFormat($environmentalTracking->date);
            })
            ->addColumn('client', function ($environmentalTracking) {
                return $environmentalTracking->customer ? $environmentalTracking->customer->name : " ";
            })
            ->addColumn('project', function ($environmentalTracking) {
                return $environmentalTracking->project ? $environmentalTracking->project->name : " ";
            })
            ->addColumn('incident', function ($environmentalTracking) {
                return Str::limit($environmentalTracking->incident_desc, 120, '...') ;
            })
            ->addColumn('root_cause', function ($environmentalTracking) {
                return Str::limit($environmentalTracking->route_course, 120, '...');
            })
            ->addColumn('status', function ($environmentalTracking) {
                return $environmentalTracking->status === 'first-aid-case' ? 'First Aid Case' : 'Lost Work Day';
            })
//            ->addColumn('pdca_cycle', function ($environmentalTracking) {
//                // $pdcaCycle= '';
//                if ($environmentalTracking->pdca_cycle == 'plan') {
//                    $pdcaCycle = "Action Identified";
//                } elseif ($environmentalTracking->pdca_cycle == 'do') {
//                    $pdcaCycle = "Action Being Implemented";
//                } elseif ($environmentalTracking->pdca_cycle == 'check') {
//                    $pdcaCycle = "Action Being Evaluated";
//                } elseif ($environmentalTracking->pdca_cycle == 'act') {
//                    $pdcaCycle = "Action Closed";
//                }
//
//                return $pdcaCycle;
//            })
            ->addColumn('resolution_time', function ($environmentalTracking) {
                return $environmentalTracking->timing . " Day(s)";
            })
            // ->addColumn('created_at', function ($environmentalTracking) {
            //     return dateFormat($environmentalTracking->created_at);
            // })
            ->addColumn('actions', function ($environmentalTracking) {
                return $environmentalTracking->action_buttons;
            })
            ->make(true);
    }
}
