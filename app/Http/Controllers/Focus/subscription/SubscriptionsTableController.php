<?php

namespace App\Http\Controllers\Focus\subscription;

use App\Http\Controllers\Controller;
use App\Models\customer\Customer;
use App\Models\subscription\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SubscriptionsTableController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Subscription::query();

        return DataTables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('tid', function ($query) {
                return gen4tid('SUB-', $query->tid);
            })
            ->addColumn('client_name', function ($query) {
                return @$query->customer->name;
            })
            ->addColumn('client_company', function ($query) {
                return @$query->customer->company;
            })
            ->addColumn('package', function ($query) {
                return @$query->package->name;
            })
            ->editColumn('start_date', function ($query) {
                return Carbon::parse($query->start_date)->format('M d, Y');
            })
            ->editColumn('end_date', function ($query) {
                return Carbon::parse($query->end_date)->format('M d, Y');
            })
            ->editColumn('status', function ($query) {
                $status = ucfirst($query->status);
                if ($query->status === 'active') {
                    return '<span class="badge bg-success">'.$status.'</span>';
                } elseif ($query->status === 'suspended') {
                    return '<span class="badge bg-warning">'.$status.'</span>';
                } elseif ($query->status === 'expired') {
                    return '<span class="badge bg-danger">'.$status.'</span>';
                }
            })
            ->addColumn('actions', function ($query) {
                return $query->action_buttons;
            })
            ->make(true);
    }
}
