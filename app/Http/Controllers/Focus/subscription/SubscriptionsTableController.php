<?php

namespace App\Http\Controllers\Focus\subscription;

use App\Http\Controllers\Controller;
use App\Models\customer\Customer;
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
        $customers = Customer::whereHas('subscriptions')->get();

        return DataTables::of($customers)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('tid', function ($model) {
                return gen4tid('CRM-', $model->tid);
            })
            ->addColumn('client_name', function ($model) {
                return $model->name;
            })
            ->addColumn('client_company', function ($model) {
                return $model->company;
            })
            ->addColumn('package', function ($model) {
                return @$model->package->name;
            })
            ->editColumn('start_date', function ($model) {
                return Carbon::parse($model->start_date)->format('d M Y H:i');
            })
            ->editColumn('end_date', function ($model) {
                return Carbon::parse($model->end_date)->format('d M Y H:i');
            })
            ->editColumn('status', function ($model) {
                $subscription = $model->subscriptions->last();
                if ($subscription)
                return '<span class="badge bg-secondary">'.$subscription->status.'</span>';
            })
            ->addColumn('actions', function ($model) {
                $subscription = $model->subscriptions->last();
                if ($subscription)
                return $subscription->action_buttons;
            })
            ->make(true);
    }
}
