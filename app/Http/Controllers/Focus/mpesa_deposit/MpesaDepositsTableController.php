<?php

namespace App\Http\Controllers\Focus\mpesa_deposit;

use App\Http\Controllers\Controller;
use App\Models\mpesa_deposit\MpesaDeposit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MpesaDepositsTableController extends Controller
{
    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $q = MpesaDeposit::query();

        $core = $q->get();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('owner_id', function ($deposit) {
                return @$deposit->tenant->cname;
            })
            ->addColumn('full_name', function ($deposit) {
                return $deposit->full_name;
            })
            ->editColumn('trans_amount', function ($deposit) {
                return numberFormat($deposit->trans_amount);
            })
            ->editColumn('trans_time', function ($deposit) {
                return date('d-M-Y H:i', strtotime($deposit->trans_time));
            })
            ->addColumn('actions', function ($deposit) {
                return $deposit->action_buttons;
            })
            ->make(true);
    }
}
