<?php

namespace App\Http\Controllers\Focus\refill_product_category;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\refill_product_category\RefillProductCategoryRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;

class RefillProductCategoriesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var RefillProductCategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param RefillProductCategoryRepository $repository ;
     */
    public function __construct(RefillProductCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('actions', function ($leave) {
                return $leave->action_buttons;
            })
            ->make(true);
    }
}
