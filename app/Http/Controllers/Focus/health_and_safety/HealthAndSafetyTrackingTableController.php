<?php

namespace App\Http\Controllers\Focus\health_and_safety;

use App\Http\Controllers\Controller;
use App\Models\health_and_safety\HealthAndSafetyTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class HealthAndSafetyTrackingTableController extends Controller
{
    const MODEL = HealthAndSafetyTracking::class;

    public function __invoke()
    {
        //
        // $core = $this->term->getForDataTable();
        // $core = HealthAndSafetyTracking::where('date', request('date'))
        //     ->where('pdca_cycle', request('pdca_cycle'))
        //     ->get();

        $core = HealthAndSafetyTracking::when(request('client_filter'), function ($q) {
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
            ->addColumn('date', function ($healthAndSafetyTracking) {
                return dateFormat($healthAndSafetyTracking->date);
            })
            ->addColumn('client', function ($healthAndSafetyTracking) {
                return $healthAndSafetyTracking->customer ? $healthAndSafetyTracking->customer->name : " ";
            })
            ->addColumn('project', function ($healthAndSafetyTracking) {
                return $healthAndSafetyTracking->project ? $healthAndSafetyTracking->project->name : " ";
            })
            ->addColumn('incident', function ($healthAndSafetyTracking) {
                return Str::limit($healthAndSafetyTracking->incident_desc, 120, '...') ;
            })
            ->addColumn('root_cause', function ($healthAndSafetyTracking) {
                return Str::limit($healthAndSafetyTracking->route_course, 120, '...');
            })
            ->addColumn('status', function ($healthAndSafetyTracking) {
                return $healthAndSafetyTracking->status === 'first-aid-case' ? 'First Aid Case' : 'Lost Work Day';
            })
//            ->addColumn('pdca_cycle', function ($healthAndSafetyTracking) {
//                // $pdcaCycle= '';
//                if ($healthAndSafetyTracking->pdca_cycle == 'plan') {
//                    $pdcaCycle = "Action Identified";
//                } elseif ($healthAndSafetyTracking->pdca_cycle == 'do') {
//                    $pdcaCycle = "Action Being Implemented";
//                } elseif ($healthAndSafetyTracking->pdca_cycle == 'check') {
//                    $pdcaCycle = "Action Being Evaluated";
//                } elseif ($healthAndSafetyTracking->pdca_cycle == 'act') {
//                    $pdcaCycle = "Action Closed";
//                }
//
//                return $pdcaCycle;
//            })
            ->addColumn('resolution_time', function ($healthAndSafetyTracking) {
                return $healthAndSafetyTracking->timing . " Day(s)";
            })
            // ->addColumn('created_at', function ($healthAndSafetyTracking) {
            //     return dateFormat($healthAndSafetyTracking->created_at);
            // })
            ->addColumn('actions', function ($healthAndSafetyTracking) {
                return $healthAndSafetyTracking->action_buttons;
            })
            ->make(true);
    }
}
