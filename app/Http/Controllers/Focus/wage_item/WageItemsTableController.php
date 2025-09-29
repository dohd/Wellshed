<?php

namespace App\Http\Controllers\Focus\wage_item;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\wage_item\WageItemRepository;

/**
 * Class BranchTableController.
 */
class WageItemsTableController extends Controller
{
    /**
     * variable to store the repository object
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     */
    public function __construct(WageItemRepository $wageItem)
    {
        $this->repository = $wageItem;
    }

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('earning_type', function ($wageItem) {
                return ucfirst($wageItem->earning_type);
            })
            ->editColumn('name', function ($wageItem) {
                return $wageItem->name;
            })
            ->editColumn('std_rate', function ($wageItem) {
                return numberFormat($wageItem->std_rate);
            })
            ->addColumn('actions', function ($wageItem) {
                return $wageItem->action_buttons;
            })
            ->make(true);
    }
}
