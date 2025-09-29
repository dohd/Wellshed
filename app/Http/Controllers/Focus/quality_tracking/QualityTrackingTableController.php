<?php

namespace App\Http\Controllers\Focus\quality_tracking;

use App\Http\Controllers\Controller;
use App\Models\health_and_safety\HealthAndSafetyTracking;
use App\Models\quality_tracking\QualityTracking;
use App\Repositories\Focus\quality_tracking\QualityTrackingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class QualityTrackingTableController extends Controller
{
    protected $qualityObjective;

    public function __construct(QualityTrackingRepository $qualityObjective)
    {
        $this->qualityObjective = $qualityObjective;
    }

    public function __invoke()
    {


        $core = QualityTracking::when(request('client_filter'), function ($q) {
                $q->where('customer_id', request('client_filter'));
            })
            ->when(request('project_filter'), function ($q) {
                $q->where('project_id', request('project_filter'));
            })
            ->when(request('month_filter'), function ($q) {
                $q->whereMonth('date', request('month_filter'));
            })
            ->get();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('date', function ($qualityTracking) {
                return dateFormat($qualityTracking->date);
            })
            ->addColumn('client', function ($qualityTracking) {
                $name = '';
                if($qualityTracking->customer){
                    $name = $qualityTracking->customer->company ?? $qualityTracking->customer->name;
                }else if($qualityTracking->client_feedback)
                {
                    $name = $qualityTracking->client_feedback->name;
                }
                return $name;
            })
            ->addColumn('project', function ($qualityTracking) {
                return $qualityTracking->project ? $qualityTracking->project->name : " ";
            })
            ->addColumn('incident', function ($qualityTracking) {
                return Str::limit($qualityTracking->incident_desc, 120, '...');;
            })
            ->addColumn('root_cause', function ($qualityTracking) {
                return Str::limit($qualityTracking->route_course, 120, '...');
            })
//            ->addColumn('pdca_cycle', function ($qualityTracking) {
//                // $pdcaCycle= '';
//                if ($qualityTracking->pdca_cycle == 'plan') {
//                    $pdcaCycle = "Action Identified";
//                } elseif ($qualityTracking->pdca_cycle == 'do') {
//                    $pdcaCycle = "Action Being Implemented";
//                } elseif ($qualityTracking->pdca_cycle == 'check') {
//                    $pdcaCycle = "Action Being Evaluated";
//                } elseif ($qualityTracking->pdca_cycle == 'act') {
//                    $pdcaCycle = "Action Closed";
//                }
//
//                return $pdcaCycle;
//            })
            ->addColumn('resolution_time', function ($qualityTracking) {
                return (string) $qualityTracking->timing . ' Day(s)';
            })
            // ->addColumn('created_at', function ($qualityTracking) {
            //     return dateFormat($qualityTracking->created_at);
            // })
            ->addColumn('actions', function ($qualityTracking) {
                return $qualityTracking->action_buttons;
            })
            ->make(true);
    }
}
