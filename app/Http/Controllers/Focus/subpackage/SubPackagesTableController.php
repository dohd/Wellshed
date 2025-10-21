<?php

namespace App\Http\Controllers\Focus\subpackage;

use App\Http\Controllers\Controller;
use App\Models\subpackage\SubPackage;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SubPackagesTableController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $subpackages = SubPackage::all();

        return DataTables::of($subpackages)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('tid', function ($model) {
                return gen4tid('PKG-', $model->tid);
            })
            ->editColumn('price', function ($model) {
                return numberFormat($model->price);
            })
            ->editColumn('is_disabled', function ($model) {
                if ($model->is_disabled)
                return '<span class="badge bg-secondary">Disabled</span>';
            })
            ->addColumn('actions', function ($model) {
                return $model->action_buttons;
            })
            ->make(true);
    }
}
