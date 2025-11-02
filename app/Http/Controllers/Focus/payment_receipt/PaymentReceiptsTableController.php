<?php

namespace App\Http\Controllers\Focus\payment_receipt;

use App\Http\Controllers\Controller;
use App\Models\payment_receipt\PaymentReceipt;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PaymentReceiptsTableController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $core = PaymentReceipt::get();
        return DataTables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('tid', function ($model) {
                return gen4tid('RCPT-', $model->tid);
            })
            ->editColumn('customer_id', function ($model) {
                return @$model->customer->company;
            })
            ->editColumn('entry_type', function ($model) {
                return ucfirst($model->entry_type);
            })
            ->editColumn('date', function ($model) {
                return dateFormat($model->date);
            })
            ->editColumn('amount', function ($model) {
                return numberFormat($model->amount);
            })
            ->addColumn('actions', function ($model) {
                return $model->action_buttons;
            })
            ->make(true);
    }
}
