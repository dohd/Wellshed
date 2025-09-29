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
namespace App\Http\Controllers\Focus\productvariable;

use App\Http\Requests\Focus\general\ManageCompanyRequest;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\productvariable\ProductvariableRepository;
use App\Http\Requests\Focus\productvariable\ManageProductvariableRequest;

/**
 * Class ProductvariablesTableController.
 */
class ProductvariablesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductvariableRepository
     */
    protected $productvariable;

    /**
     * contructor to initialize repository object
     * @param ProductvariableRepository $productvariable ;
     */
    public function __construct(ProductvariableRepository $productvariable)
    {
        $this->productvariable = $productvariable;
    }

    /**
     * This method return the data of the model
     * @param ManageProductvariableRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageCompanyRequest $request)
    {
        $core = $this->productvariable->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('total', function ($productvariable) {
                return $productvariable->products->count();
            })
            ->addColumn('base_ratio', function ($productvariable) use($core) {
                $ratio = numberFormat($productvariable->base_ratio);
                if ($productvariable->unit_type == 'base') return $ratio;

                $base_unit = $core->filter(function ($v) use($productvariable) {
                    return $v->id == $productvariable->base_unit_id;
                })->first();

                return $base_unit? "{$ratio} / {$base_unit->code}" : $ratio;
            })
            ->addColumn('actions', function ($productvariable) {
                return $productvariable->action_buttons;
            })
            ->make(true);
    }
}
