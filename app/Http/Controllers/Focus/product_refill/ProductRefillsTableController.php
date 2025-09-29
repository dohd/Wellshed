<?php

namespace App\Http\Controllers\Focus\product_refill;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\product_refill\ProductRefillRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class ProductRefillsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductRefillRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductRefillRepository $repository ;
     */
    public function __construct(ProductRefillRepository $repository)
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
            ->addColumn('customer', function ($refill) {
                return @$refill->product_customer->name;
            })
            ->addColumn('actions', function ($refill) {
                return $refill->action_buttons;
            })
            ->make(true);
    }
}
